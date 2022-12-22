<?php
namespace App\Services\Editable;

use Illuminate\Database\Eloquent\Model;

/**
* Model of the table tasks.
*/
class EditHistory extends Model
{
    protected $table = 'edit_histories';

    protected $guarded = [];


}
