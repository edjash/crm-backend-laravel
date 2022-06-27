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
            'contactType' => 'string',
            'contactId' => 'numeric',
        ])->validate();

        $userId = $request->user()->id;

        $data = [
            'contact_id' => ($validatedData['contactType'] === 'contact') ? $validatedData['contactId'] : null,
            'company_id' => ($validatedData['contactType'] === 'company') ? $validatedData['contactId'] : null,
            'content' => $validatedData['content'],
            'updated_by' => $userId,
        ];

        if ($noteId) {
            $model = Notes::find($noteId);
            $model->fill($data);
            $model->save();
        } else {
            $data['created_by'] = $userId;
            $model = Notes::create($data);
        }

        return response()->json(["note" => $model, "noteId" => $noteId]);
    }

    public function delete(Request $request, $noteId)
    {
        Notes::destroy($noteId);
        return response()->json('OK');
    }
}
