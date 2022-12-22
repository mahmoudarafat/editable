<?php

namespace App\Services\Editable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait Historyable
{
    public static function bootHistoryable(){
        static::updated( function(Model $model){

            collect($model->getWantedChangedColumns($model))->each(function ($change) use($model){
                $model->saveChange($change);
            });
        } );
    }

    public function saveChange(ColumnChange $change){

        $this->history()->create([
            'column_changed' => $change->column,
            'original_value' =>$this->valueToString($change->from),
            'new_value' => $this->valueToString($change->to),
            'updated_by' => auth()->check() ? auth()->id() : 'guest',
            'shop_id'   => auth()->check() ? auth()->user()->shop_id : 0,
        ]);
    }

    protected function getWantedChangedColumns(Model $model){

        $changes = $model->getChanges();
        $ignored = $this->getIgnoredColumns($model);
        $toTrack = [];
        $original = $model->getOriginal();

        foreach($changes as $key => $val){
            if(! in_array($key, $ignored) ){
                $toTrack[] = new ColumnChange($key, $this->valueToString(Arr::get($original, $key)),  $this->valueToString($val) );
            }
        }
        return $toTrack;

    }

    public function history()
    {
        return $this->morphMany(EditHistory::class, 'historyable')->latest();
    }

    public function getIgnoredColumns($model)
    {
        return $model->ignoreHistoryColumns ?? ['updated_at'];
    }

    public function valueToString($value){
        $type = gettype($value);
        if($type == 'boolean'){
            $value = $value ? '1' : '0';
        }
        if( in_array($type, ['NULL', 'null'])){
            $value = '';
        }
        return (string) $value;
    }

}
