<?php

namespace App\Http\Controllers;

use App\Http\Traits\ArrayFieldsTrait;
use App\Http\Traits\AvatarTrait;
use App\Http\Traits\NotesTrait;
use App\Models\Contact;
use App\Models\SocialMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    use AvatarTrait, ArrayFieldsTrait, NotesTrait;

    public function index(Request $request)
    {
        $fields = explode(",", $request->input('fields'));
        $search = $request->input('search');

        $contacts = Contact::query();

        $relations = ["address", "phone_number", "email_address"];
        foreach ($relations as $field) {
            if (in_array($field, $fields)) {
                $contacts->with([Str::camel($field) => function ($query) {
                    $query->whereNull('company_id');
                }]);
            }
        }

        if ($search) {
            $contacts->where('contacts.fullname', 'LIKE', "%{$search}%");

            if (in_array('address', $fields)) {
                $contacts->orWhereHas('address', function ($query) use ($search) {
                    $query->where([
                        ['full_address', 'LIKE', "%{$search}%"],
                    ])->whereNull('company_id');
                });
            }
        }

        return $contacts->paginate($request->limit);
    }

    public function getContact(Request $request, $id)
    {
        $contact = Contact::with(
            [
                'address.country',
                'emailAddress',
                'phoneNumber',
                'socialMediaUrl',
                'company',
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

        $model = Contact::create([
            'title' => $validatedData['title'] ?? '',
            'pronouns' => $validatedData['pronouns'] ?? '',
            'firstname' => $validatedData['firstname'] ?? "",
            'lastname' => $validatedData['lastname'] ?? "",
            'company_id' => $validatedData['company'] ?? null,
            'avatar' => $this->savePermAvatar($validatedData['avatar'] ?? ''),
        ]);

        $this->arrayFieldsUpsert('company_id', $model->id, [
            'Address' => $validatedData['address'] ?? [],
            'EmailAddress' => $validatedData['email_address'] ?? [],
            'PhoneNumber' => $validatedData['phone_number'] ?? [],
        ]);
        $this->saveSocialMedia($validatedData['socialmedia'] ?? [], $model->id);

        return response()->json(["company" => $model]);
    }

    public function update(Request $request, int $id)
    {
        $validatedData = $this->validateData($request);
        $validatedData['avatar'] = $this->savePermAvatar($validatedData['avatar'] ?? '');
        $validatedData['company_id'] = $validatedData['company'] ?? null;
        $model = Contact::find($id);
        $model->fill($validatedData);
        $model->save();
        $this->arrayFieldsDelete([
            'Address' => $validatedData['address_deleted'] ?? [],
            'EmailAddress' => $validatedData['email_address_deleted'] ?? [],
            'PhoneNumber' => $validatedData['phone_number_deleted'] ?? [],
        ]);
        $this->arrayFieldsUpsert('contact_id', $id, [
            'Address' => $validatedData['address'] ?? [],
            'EmailAddress' => $validatedData['email_address'] ?? [],
            'PhoneNumber' => $validatedData['phone_number'] ?? [],
        ]);
        $this->saveSocialMedia($validatedData['socialmedia'] ?? [], $model->id);
        $this->saveNote($request, 'contact', $id);
        return response()->json(["company" => $model]);
    }

    public function delete(Request $request, $ids)
    {
        $ids = array_map('intval', explode(",", $ids));
        Contact::destroy($ids);

        return $this->index($request);
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
            'nickname' => 'max:255',
            'company' => 'max:11|nullable',
            'address.*.id' => 'numeric|nullable',
            'address.*.label' => 'string|max:255',
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
}
