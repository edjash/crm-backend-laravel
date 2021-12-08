<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Country;

class Address extends Model
{
    use HasFactory;

    protected $table = 'addresses';

    protected $fillable = [
        'type',
        'contact_id',
        'company_id',
        'street',
        'town',
        'county',
        'postcode',
        'country_code',
        'country_name',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    protected static function boot()
    {
        parent::boot();
        Address::saving(function ($model) {

            $address = [
                "street" => $model->street,
                "town" => $model->town,
                "county" => $model->county,
                "postcode" => $model->postcode,
            ];

            if ($model->country_code) {
                $country = Country::where('code', $model->country_code)->first();
                if ($country) {
                    $model->country_name = $country->name;
                    $address['country_name'] = $country->name;
                }
            }

            $address = array_filter($address);
            if (count($address)) {
                $model->full_address = implode(", ", $address);
            }
        });
    }
}
