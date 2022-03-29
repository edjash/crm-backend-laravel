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
        'nickname',
        'avatar',
        'company_id',
    ];

    public function address()
    {
        return $this->hasMany(Address::class);
    }

    public function company()
    {
        return $this->hasOne(Company::class, 'id', 'company_id');
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

            //avatar was deleted by user
            if (!$model->avatar && $model->getOriginal('avatar')) {
                Storage::delete('public/avatars/large' . $model->getOriginal('avatar'));
                Storage::delete('public/avatars/medium' . $model->getOriginal('avatar'));
                Storage::delete('public/avatars/small' . $model->getOriginal('avatar'));
            }
        });

        Contact::deleting(function ($model) {
            if ($model->avatar) {
                Storage::delete('public/avatars/large' . $model->avatar);
                Storage::delete('public/avatars/medium' . $model->avatar);
                Storage::delete('public/avatars/small' . $model->avatar);
            }
        });
    }
}
