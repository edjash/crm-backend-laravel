<?php

namespace Database\Factories;

use App\Models\EmailAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailAddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailAddress::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $email = $this->faker->email();
        while (true) {
            if (!strpos($email, 'gaylord')) {
                break;
            }
            $email = $this->faker->email();
        }

        $email_address = [
            'address' => $email,
        ];

        return $email_address;
    }
}
