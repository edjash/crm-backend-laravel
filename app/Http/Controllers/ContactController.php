<?php

namespace App\Http\Controllers;

use App\Models\Contact;
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
                ->orWhereHas('address', function($query) use ($term) {
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
        //
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
