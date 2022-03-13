<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\SocialMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $term = $request->input('search');
        if (!$term) {
            return Contact::with(
                [
                    'address' => function ($query) {
                        $query->whereNull('company_id');
                    },
                    'phoneNumber' => function ($query) {
                        $query->whereNull('company_id');
                    },
                    'emailAddress' => function ($query) {
                        $query->whereNull('company_id');
                    },
                ]
            )->paginate($request->limit);
        } else {
            $builder = Contact::with(['address' => function ($query) {
                $query->whereNull('company_id');
            }])->where('contacts.fullname', 'LIKE', "%{$term}%")
                ->orWhereHas('address', function ($query) use ($term) {
                    $query->where([
                        ['full_address', 'LIKE', "%{$term}%"],
                    ])->whereNull('company_id');
                });

            return $builder->paginate($request->limit);
        }
    }

    public function getContact(Request $request, $id)
    {
        $contact = Contact::with(
            [
                'address.country',
                'emailAddress',
                'phoneNumber',
                'socialMediaUrl',
            ]
        )->find($id)->toArray();

        foreach ($contact['address'] as $index => $address) {
            if ($address['country']) {
                $address['country_code'] = $address['country']['code'];
                $address['country_name'] = $address['country']['name'];
            }
            unset($address['country']);
            $contact['address'][$index] = $address;
        }

        return response()->json($contact);
    }

    public function create(Request $request)
    {
        $validatedData = $this->validateData($request);

        $contact = Contact::create([
            'title' => $validatedData['title'] ?? '',
            'pronouns' => $validatedData['pronouns'] ?? '',
            'firstname' => $validatedData['firstname'] ?? "",
            'lastname' => $validatedData['lastname'] ?? "",
            'avatar' => $this->saveAvatar($validatedData['avatar'] ?? ''),
        ]);

        $this->insertUpdateArrayItems('Address', $validatedData['address'] ?? [], $contact->id);
        $this->insertUpdateArrayItems('EmailAddress', $validatedData['email_address'] ?? [], $contact->id);
        $this->insertUpdateArrayItems('PhoneNumber', $validatedData['phone_number'] ?? [], $contact->id);
        $this->saveSocialMedia($validatedData['socialmedia'] ?? [], $contact->id);

        return response()->json(["contact" => $contact]);
    }

    public function update(Request $request, int $id)
    {
        $validatedData = $this->validateData($request);
        $validatedData['avatar'] = $this->saveAvatar($validatedData['avatar'] ?? '');
        $contact = Contact::find($id);
        $contact->fill($validatedData);
        $contact->save();
        $this->deleteArrayItems('Address', $validatedData['address_deleted'] ?? []);
        $this->deleteArrayItems('EmailAddress', $validatedData['email_address_deleted'] ?? []);
        $this->deleteArrayItems('PhoneNumber', $validatedData['phone_number_deleted'] ?? []);
        $this->insertUpdateArrayItems('Address', $validatedData['address'] ?? [], $contact->id);
        $this->insertUpdateArrayItems('EmailAddress', $validatedData['email_address'] ?? [], $contact->id);
        $this->insertUpdateArrayItems('PhoneNumber', $validatedData['phone_number'] ?? [], $contact->id);
        $this->saveSocialMedia($validatedData['socialmedia'] ?? [], $contact->id);

        return response()->json(["contact" => $contact]);
    }

    public function delete(Request $request, $ids)
    {
        $ids = array_map('intval', explode(",", $ids));
        Contact::destroy($ids);

        return $this->index($request);
    }

    public function uploadAvatar(Request $request)
    {
        if (!is_writable(storage_path('app/public/tmp_avatars'))) {
            return response()->json([
                "error" => "No filesystem permission to store temporary avatar.",
            ], 500);
        }

        Validator::make($request->file(), [
            'avatar' => 'mimes:jpeg,jpg,png,gif|max:10000',
        ])->validate();

        $file = $request->file('avatar');
        $hashName = 'tmp_' . $file->hashName();
        $path = $file->storePubliclyAs('public/tmp_avatars', $hashName);

        return response()->json(["filename" => basename($path)]);
    }

    //Utility functions
    private function validateData(Request $request)
    {
        return Validator::make($request->all(), [
            'avatar' => 'max:255',
            'title' => 'max:255',
            'pronouns' => 'max:255',
            'firstname' => 'required|max:255',
            'lastname' => 'max:255',
            'address.*.id' => 'numeric|nullable',
            'address.*.label' => 'max:255',
            'address.*.street' => 'max:255',
            'address.*.town' => 'max:255',
            'address.*.county' => 'max:255',
            'address.*.postcode' => 'max:255',
            'address.*.country' => 'max:3',
            'address_deleted' => 'array|nullable',
            'email_address.*.id' => 'numeric|nullable',
            'email_address.*.label' => 'max:255',
            'email_address.*.address' => 'max:255',
            'email_address_deleted' => 'array|nullable',
            'phone_number.*.id' => 'numeric|nullable',
            'phone_number.*.label' => 'max:255',
            'phone_number.*.number' => 'max:255',
            'phone_number_deleted' => 'array|nullable',
            'socialmedia.*' => 'string|nullable|max:255',
        ])->validate();
    }

    private function deleteArrayItems($modelName, array $list)
    {
        $model = "App\\Models\\$modelName";
        $list = array_unique($list);

        if (!count($list)) {
            return;
        }

        foreach ($list as $id) {
            $id = intval(trim($id));
            if ($id) {
                $model::destroy($id);
            }
        }
    }

    private function insertUpdateArrayItems($modelName, array $list, $contact_id)
    {
        $model = "App\\Models\\$modelName";

        if (!count($list)) {
            return;
        }

        foreach ($list as $index => $data) {

            $data["contact_id"] = $contact_id;
            $data["display_index"] = $index;

            if (!($data['id'] ?? false)) {
                if (!$model::isEmpty($data)) {
                    $model::create($data);
                }
                continue;
            }

            $id = intval($data['id']);
            unset($data['id']);

            $instance = $model::find($id);

            if (!$instance || $instance->contact_id != $contact_id) {
                continue;
            }

            if ($model::isEmpty($data)) {
                $model::destroy($id);
                continue;
            }

            $instance->fill($data);
            $instance->save();
            continue;
        }
    }

    private function saveSocialMedia($data, $contact_id)
    {
        foreach ($data as $ident => $url) {
            if (!in_array($ident, ['facebook', 'instagram', 'twitter', 'linkedin'])) {
                continue;
            }
            SocialMediaUrl::updateOrCreate(
                ["contact_id" => $contact_id, "ident" => $ident],
                ["ident" => $ident, "url" => $url]
            );
        }
    }

    private function saveAvatar($file): string
    {
        if (!$file || (substr($file, 0, 4) !== 'tmp_')) {
            return $file;
        }

        if (!is_writable(storage_path('app/public/avatars'))) {
            Log::error(storage_path('app/public/avatars') . ' is not writeable');
            return '';
        }

        $tmppath = 'public/tmp_avatars/' . $file;
        if (!Storage::exists($tmppath)) {
            return '';
        }

        $newfile = str_replace('tmp_', '', $file);
        if (!Storage::move(
            $tmppath,
            'public/avatars/' . $newfile
        )) {
            return '';
        }

        Storage::delete($tmppath);

        return $newfile;
    }
}
