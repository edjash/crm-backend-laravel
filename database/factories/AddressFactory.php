<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $countryCode = $this->faker->countryCode();
        $countryName = "";
        while ($countryName == null) {
            $countryCode = $this->faker->countryCode();
            $countryName = DB::table('countries')->where('code', $countryCode)->value('name');
        }

        $address = [
            'street' => $this->faker->streetAddress(),
            'town' => $this->faker->city(),
            'county' => '',
            'postcode' => $this->faker->postcode(),
        ];

        $address['country_code'] = $countryCode;
        $address['type'] = 'main';

        return $address;
    }
}
