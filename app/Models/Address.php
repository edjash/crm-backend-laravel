<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Country;
use phpDocumentor\Reflection\Types\Boolean;

class Address extends Model
{
    use HasFactory;

    protected $table = 'addresses';

    protected $fillable = [
        'contact_id',
        'company_id',
        'street',
        'town',
        'county',
        'postcode',
        'country_code',
        'country_name',
        'full_address',
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

    public function country()
    {
        return $this->hasOne(Country::class, 'code', 'country_code');
    }

    public static function isEmpty($address): bool
    {
        $required = ['street', 'town', 'county', 'postcode', 'country_code'];
        $values = [];
        foreach ($required as $item) {
            $values[] = $address[$item] ?? '';
        }
        return (count(array_filter($values)) === 0);
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
                "country_name" => "",
            ];

            if ($model->country_code) {
                $country = Country::where('code', $model->country_code)->first();
                if ($country) {
                    $address['country_name'] = $country->name;
                    $model->country_name = $country->name;
                }
            }

            $fulladdress = array_filter($address);
            if (count($fulladdress)) {
                $model->full_address = implode(", ", $fulladdress);
            }

            if (!$model->display_index) {
                $model->display_index = 0;
            }
        });
    }
}
