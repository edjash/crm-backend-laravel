<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'street' => 'string|max:255',
            'town' => 'string|max:255',
            'county' => 'string|max:255',
            'postcode' => 'string|max:255',
            'country_code' => 'string|max:255',
        ]);

        $company = Company::create([
            'name' => $validatedData['name'],
        ]);

        $addr = [
            "type" => "main",
            "company_id" => $company->id,
            "street" => $validatedData['street'] ?? "",
            "town" => $validatedData['town'] ?? "",
            "county" => $validatedData['county'] ?? "",
            "postcode" => $validatedData['postcode'] ?? "",
            "country_code" => $validatedData['country_code'] ?? "",
        ];

        $address = new Address();
        $address->fill($addr);
        $address->save();

        return response()->json(["company" => $company]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $Company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $Company)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $Company
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, $ids)
    {
        $ids = array_map('intval', explode(",", $ids));
        Company::destroy($ids);

        return $this->getCompanies($request);
    }
}
