<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return response()->json($this->getContacts($request));
    }

    public function getContacts(Request $request)
    {
        $term = $request->input('search');
        if (!$term) {
            return Contact::with(['address' => function ($query) {
                $query->where('type', '=', 'main');
            }])->paginate($request->limit);
        } else {
            $builder = Contact::with(['address' => function ($query) {
                $query->where('type', '=', 'main');
            }])->where('contacts.fullname', 'LIKE', "%{$term}%")
                ->orWhereHas('address', function ($query) use ($term) {
                    $query->where('full_address', 'LIKE', "%{$term}%");
                });


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
            'firstname' => 'required|string|max:255',
            'lastname' => 'string|max:255',
            'street' => 'string|max:255',
            'town' => 'string|max:255',
            'county' => 'string|max:255',
            'postcode' => 'string|max:255',
            'country_code' => 'string|max:255',
        ]);

        $contact = Contact::create([
            'firstname' => $validatedData['firstname'],
            'lastname' => $validatedData['lastname'] ?? "",
        ]);

        $address = [
            "type" => "main",
            "contact_id" => $contact->id,
            "street" => $validatedData['street'] ?? "",
            "town" => $validatedData['town'] ?? "",
            "county" => $validatedData['county'] ?? "",
            "postcode" => $validatedData['postcode'] ?? "",
            "country_code" => $validatedData['country_code'] ?? ""
        ];

        DB::table('addresses')->insert($address);

        return response()->json(["contact" => $contact]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Contact $contact)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function delete($ids)
    {
        $ids = array_map('intval', explode(",", $ids));
        $deleted = Contact::destroy($ids);

        return response()->json(["deleted" => $ids]);
    }
}
