<?php

namespace App\Http\Controllers;

use App\Http\Traits\ArrayFieldsTrait;
use App\Http\Traits\AvatarTrait;
use App\Models\Company;
use App\Models\SocialMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    use AvatarTrait, ArrayFieldsTrait;

    public function index(Request $request)
    {
        $term = $request->input('search');
        if (!$term) {
            return Company::with(
                [
                    'address' => function ($query) {
                        $query->whereNull('contact_id');
                    },
                    'phoneNumber' => function ($query) {
                        $query->whereNull('contact_id');
                    },
                    'emailAddress' => function ($query) {
                        $query->whereNull('contact_id');
                    },
                ]
            )->paginate($request->limit);
        } else {
            $builder = Company::with(['address' => function ($query) {
                $query->whereNull('contact_id');
            }])->where('companies.name', 'LIKE', "%{$term}%")
                ->orWhereHas('address', function ($query) use ($term) {
                    $query->where([
                        ['full_address', 'LIKE', "%{$term}%"],
                    ])->whereNull('contact_id');
                });

            return $builder->paginate($request->limit);
        }
    }

    public function getCompany(Request $request, $id)
    {
        $company = Company::with(
            [
                'address.country',
                'emailAddress',
                'phoneNumber',
                'socialMediaUrl',
                'industry',
            ]
        )->find($id)->toArray();

        foreach ($company['address'] as $index => $address) {
            if ($address['country']) {
                $address['country_code'] = $address['country']['code'];
                $address['country_name'] = $address['country']['name'];
            }
            unset($address['country']);
            $company['address'][$index] = $address;
        }

        return response()->json($company);
    }

    public function create(Request $request)
    {
        $validatedData = $this->validateData($request);

        $model = Company::create([
            'name' => $validatedData['name'] ?? "",
            'industry_id' => $validatedData['industry'] ?? null,
            'avatar' => $this->saveAvatar($validatedData['avatar'] ?? ''),
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
        $validatedData['avatar'] = $this->saveAvatar($validatedData['avatar'] ?? '');
        $validatedData['industry_id'] = $validatedData['industry'] ?? null;
        $model = Company::find($id);
        $model->fill($validatedData);
        $model->save();

        $this->arrayFieldsDelete([
            'Address' => $validatedData['address_deleted'] ?? [],
            'EmailAddress' => $validatedData['email_address_deleted'] ?? [],
            'PhoneNumber' => $validatedData['phone_number_deleted'] ?? [],
        ]);
        $this->arrayFieldsUpsert('company_id', $id, [
            'Address' => $validatedData['address'] ?? [],
            'EmailAddress' => $validatedData['email_address'] ?? [],
            'PhoneNumber' => $validatedData['phone_number'] ?? [],
        ]);
        $this->saveSocialMedia($validatedData['socialmedia'] ?? [], $model->id);

        return response()->json(["company" => $model]);
    }

    public function delete(Request $request, $ids)
    {
        $ids = array_map('intval', explode(",", $ids));
        Company::destroy($ids);

        return $this->index($request);
    }
    //Utility functions
    private function validateData(Request $request)
    {
        return Validator::make($request->all(), [
            'avatar' => 'max:255',
            'name' => 'required|max:255',
            'industry_id' => 'max:11',
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

    private function saveSocialMedia($data, $company_id)
    {
        foreach ($data as $ident => $url) {
            if (!in_array($ident, ['facebook', 'instagram', 'twitter', 'linkedin'])) {
                continue;
            }
            SocialMediaUrl::updateOrCreate(
                ["company_id" => $company_id, "ident" => $ident],
                ["ident" => $ident, "url" => $url]
            );
        }
    }
}
