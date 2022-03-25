<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->company;

        return [
            'name' => $name,
            'industry_id' => rand(1, 20),
        ];
    }
}
