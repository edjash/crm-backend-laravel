<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Support\Facades\DB;
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
        return response()->json($this->getCompanies($request));
    }

    public function getCompanies(Request $request)
    {
        $term = $request->input('search');
        if (!$term) {
            return Company::with(['address' => function ($query) {
                $query->where('type', '=', 'main');
            }])->paginate($request->limit);
        } else {
            $builder = Company::with(['address' => function ($query) {
                $query->where('type', '=', 'main');
            }])->where('Companies.name', 'LIKE', "%{$term}%");

            // return ["sql" => $builder->toSql(), "bindings" => $builder->getBindings(), "data" => []];
            return $builder->paginate($request->limit);
        }
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

        $address = [
            "type" => "main",
            "company_id" => $company->id,
            "street" => $validatedData['street'] ?? "",
            "town" => $validatedData['town'] ?? "",
            "county" => $validatedData['county'] ?? "",
            "postcode" => $validatedData['postcode'] ?? "",
            "country_code" => $validatedData['country_code'] ?? ""
        ];

        DB::table('addresses')->insert($address);

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
    public function delete($ids)
    {
        $ids = array_map('intval', explode(",", $ids));
        $deleted = Company::destroy($ids);

        return response()->json(["deleted" => $ids]);
    }
}
