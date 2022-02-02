<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $genders = ['male', 'female'];
        $gender = $genders[rand(0, 1)];

        $title = str_replace('.', '', $this->faker->title($gender));
        $firstName = $this->faker->firstName($gender);
        $lastName = $this->faker->lastName();

        return [
            'title' => $title,
            'firstname' => $firstName,
            'lastname' => $lastName,
        ];
    }
}
