<?php
namespace App\Http\Traits;

use App\Models\Notes;
use Illuminate\Http\Request;

trait NotesTrait
{
    public function saveNote(Request $request, $contactType, $contactId)
    {
        if (!$request->has('note', 'noteId')) {
            return;
        }
        $noteId = intval($request->noteId);
        $noteContent = trim($request->note);
        $userId = $request->user()->id;

        if ($noteId) {
            $note = Notes::find($noteId);
            if ($noteContent != $note->content) {
                $note->fill([
                    'content' => $noteContent,
                    'updated_by' => $userId,
                ]);
                $note->save();
            }
        } else {
            $note = Notes::create([
                'contact_id' => ($contactType === 'contact') ? $contactId : null,
                'company_id' => ($contactType === 'company') ? $contactId : null,
                'content' => $noteContent,
                'updated_by' => $userId,
            ]);
        }

        return $note;
    }
}
