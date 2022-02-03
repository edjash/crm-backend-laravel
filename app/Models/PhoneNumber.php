<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneNumber extends Model
{
    use HasFactory;

    protected $table = 'phone_numbers';

    protected $fillable = [
        'contact_id',
        'company_id',
        'number',
        'label',
        'display_index',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function isEmpty($data)
    {
        return (!strlen($data['number']));
    }

    protected static function boot()
    {
        parent::boot();
        Address::saving(function ($model) {
            if (!$model->display_index) {
                $model->display_index = 0;
            }
        });
    }
}
