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
            'county' => $this->faker->state(),
            'postcode' => $this->faker->postcode(),
            'country_name' => $countryName,
            'full_address' => '',
        ];

        $full_address = array_filter($address);
        $address['country'] = $countryCode;
        $address['full_address'] = implode(", ", $full_address);

        return $address;
    }
}
