<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\DB;

trait ArrayFieldsTrait
{
    public function arrayFieldsUpsert(string $recordIdField, int $recordId, array $fields)
    {

        foreach ($fields as $modelName => $fieldList) {
            if (!is_array($fieldList)) {
                continue;
            }

            $model = "App\\Models\\$modelName";
            foreach ($fieldList as $index => $fieldData) {

                $id = intval($fieldData['id'] ?? 0);
                unset($fieldData['id']);
                $fieldData["display_index"] = $index;
                $fieldData[$recordIdField] = $recordId;

                if (!$id) {
                    //this is a new field, check if data is blank, if not insert it.
                    if (!$model::isEmpty($fieldData)) {
                        $table = app($model)->getTable();
                        //Note: using $model::create($fieldData) caused php out of memory here.
                        DB::table($table)->insert($fieldData);
                    }
                    continue;
                }

                $instance = $model::find($id);
                if (!$instance || $instance->$recordIdField != $recordId) {
                    continue;
                }

                if ($model::isEmpty($fieldData)) {
                    $model::destroy($id);
                    continue;
                }

                $instance->fill($fieldData);
                $instance->save();
            }
        }
    }

    public function arrayFieldsDelete(array $list)
    {
        foreach ($list as $modelName => $ids) {
            if (!is_array($ids)) {
                continue;
            }

            $ids = array_unique($ids);

            if (!count($ids)) {
                continue;
            }

            foreach ($ids as $id) {
                $id = intval(trim($id));
                if ($id) {
                    $model = "App\\Models\\$modelName";
                    $model::destroy($id);
                }
            }
        }
    }
}
