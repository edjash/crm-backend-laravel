<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Contact;
use App\Models\SocialMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($this->getContacts($request));
    }

    public function getContacts(Request $request)
    {
        $term = $request->input('search');
        if (!$term) {
            return Contact::with(['address' => function ($query) {
                $query->whereNull('company_id');
            }])->paginate($request->limit);
        } else {
            $builder = Contact::with(['address' => function ($query) {
                $query->whereNull('company_id');
            }])->where('contacts.fullname', 'LIKE', "%{$term}%")
                ->orWhereHas('address', function ($query) use ($term) {
                    $query->where([
                        ['full_address', 'LIKE', "%{$term}%"]
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
        )->find($id);

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
        ]);

        $this->insertUpdateItems('Address', $validatedData['address'] ?? [], $contact->id);
        $this->insertUpdateItems('EmailAddress', $validatedData['email'] ?? [], $contact->id);
        $this->insertUpdateItems('PhoneNumber', $validatedData['phone'] ?? [], $contact->id);

        return response()->json(["contact" => $contact]);
    }

    public function update(Request $request, int $id)
    {
        $validatedData = $this->validateData($request);

        if ($avatar = $this->saveAvatar($validatedData['avatar'])) {
            $validatedData['avatar'] = $avatar;
        }

        $contact = Contact::find($id);
        $contact->fill($validatedData);
        $contact->save();

        $this->deleteItems('Address', $validatedData['address_deleted'] ?? []);
        $this->deleteItems('EmailAddress', $validatedData['email_address_deleted'] ?? []);
        $this->deleteItems('PhoneNumber', $validatedData['phone_deleted'] ?? []);
        $this->insertUpdateItems('Address', $validatedData['address'] ?? [], $contact->id);
        $this->insertUpdateItems('EmailAddress', $validatedData['email_address'] ?? [], $contact->id);
        $this->insertUpdateItems('PhoneNumber', $validatedData['phone'] ?? [], $contact->id);

        foreach ($validatedData['socialmedia'] ?? [] as $ident => $url) {
            if (!in_array($ident, ['facebook', 'instagram', 'twitter', 'linkedin'])) {
                continue;
            }
            SocialMediaUrl::updateOrCreate(
                ["contact_id" => $contact->id, "ident" => $ident],
                ["ident" => $ident, "url" => $url]
            );
        }

        return response()->json(["contact" => $contact]);
    }

    public function delete(Request $request, $ids)
    {
        $ids = array_map('intval', explode(",", $ids));
        Contact::destroy($ids);

        return $this->getContacts($request);
    }

    public function avatar(Request $request, $id)
    {
        if (!$id) {
            return response("Contact ID expected", 400);
        }

        Validator::make($request->file(), [
            'avatar' => 'mimes:jpeg,jpg,png,gif|max:10000'
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
            'address_deleted' => 'string|nullable',
            'email_address.*.id' => 'numeric|nullable',
            'email_address.*.label' => 'max:255',
            'email_address.*.address' => 'max:255',
            'email_address_deleted' => 'array|nullable',
            'phone.*.id' => 'numeric|nullable',
            'phone.*.label' => 'max:255',
            'phone.*.number' => 'max:255',
            'phone_deleted' => 'string|nullable',
            'socialmedia.*' => 'max:255',
        ])->validate();
    }

    private function deleteItems($modelName, array $list)
    {
        $model = "App\\Models\\$modelName";
        $list  = array_unique($list);

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

    private function insertUpdateItems($modelName, array $list, $contact_id)
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

    private function saveAvatar($tmpfile)
    {
        if (!$tmpfile || substr($tmpfile, 0, 4) != 'tmp_') {
            return false;
        }

        $tmppath = 'public/tmp_avatars/' . $tmpfile;
        if (!Storage::exists($tmppath)) {
            return false;
        }

        $newfile = str_replace('tmp_', '', $tmpfile);
        if (!Storage::move(
            $tmppath,
            'public/avatars/' . $newfile
        )) {
            return false;
        }

        Storage::delete($tmppath);

        return $newfile;
    }
}
