<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMediaUrl extends Model
{
    use HasFactory;

    protected $table = 'socialmedia_urls';

    protected $fillable = [
        'contact_id',
        'company_id',
        'ident',
        'url',
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
        return (!strlen($data['url']));
    }

    protected static function boot()
    {
        parent::boot();
    }
}
