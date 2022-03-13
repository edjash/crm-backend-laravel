<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'pronouns',
        'firstname',
        'lastname',
        'fullname',
        'avatar',
    ];

    public function address()
    {
        return $this->hasMany(Address::class);
    }

    public function emailAddress()
    {
        return $this->hasMany(EmailAddress::class);
    }

    public function phoneNumber()
    {
        return $this->hasMany(PhoneNumber::class);
    }

    public function socialMediaUrl()
    {
        return $this->hasMany(SocialMediaUrl::class);
    }

    protected static function boot()
    {
        parent::boot();

        Contact::saving(function ($model) {
            $fullname = trim($model->firstname . ' ' . $model->lastname);
            $model->fullname = $fullname;

            if (!$model->avatar && $model->getOriginal('avatar')) {
                Storage::delete('public/avatars/' . $model->getOriginal('avatar'));
            }
        });

        Contact::deleting(function ($model) {
            if ($model->avatar) {
                Storage::delete('public/avatars/' . $model->avatar);
            }
        });
    }
}
