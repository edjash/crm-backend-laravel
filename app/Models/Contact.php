<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'firstname',
        'lastname',
        'fullname',
    ];


    public function address()
    {
        return $this->hasMany(Address::class);
    }

    public function emailAddress()
    {
        return $this->hasMany(EmailAddress::class);
    }

    protected static function boot()
    {
        parent::boot();
        Contact::saving(function ($model) {
            $fullname = trim($model->firstname . ' ' . $model->lastname);
            $model->fullname = $fullname;
        });
    }
}
