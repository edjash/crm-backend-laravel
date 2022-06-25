<?php

namespace App\Http\Controllers;

use App\Models\Notes;
use Illuminate\Http\Request;

class NotesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $contactType, $contactId)
    {
        $field = ($contactType === 'contact') ? 'contact_id' : 'company_id';

        $notes = Notes::where($field, '=', $contactId)->paginate($request->limit);

        return response()->json($notes);
    }

}
