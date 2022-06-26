<?php

namespace App\Http\Controllers;

use App\Models\Notes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        $q = Notes::where($field, '=', $contactId);
        $q->orderBy('updated_at', 'DESC');
        $notes = $q->paginate($request->limit);

        return response()->json($notes);
    }

    public function save(Request $request, $noteId = null)
    {
        $validatedData = Validator::make($request->all(), [
            'content' => 'string|nullable',
        ])->validate();

        if ($noteId) {
            $model = Notes::find($noteId);
            $model->fill($validatedData);
            $model->save();
        } else {
            $model = Notes::create([
                'content' => $validatedData['content'],
            ]);
        }

        return response()->json(["note" => $model, "noteId" => $noteId]);
    }

}
