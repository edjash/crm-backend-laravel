<?php

namespace Database\Factories;

use App\Models\PhoneNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhoneNumberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PhoneNumber::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $number = $this->faker->phoneNumber();
        while (true) {
            if (substr_count($number, '-') < 2 && !strpos($number, '.')) {
                break;
            }
            $number = $this->faker->phoneNumber();
        }

        $phone_number = [
            'number' => $number,
        ];

        return $phone_number;
    }
}
