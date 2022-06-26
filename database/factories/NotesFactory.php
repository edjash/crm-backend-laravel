<?php

namespace Database\Factories;

use App\Models\Notes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notes::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */

    public function definition()
    {
        $text = $this->faker->realText(rand(50, 400));
        $date = Carbon::createFromTimestamp(
            rand(strtotime('-1 Month'), strtotime('-1 day'))
        );

        $note = [
            'content' => $text,
            'created_by' => 1,
            'created_at' => $date,
            'updated_at' => $date,
        ];

        return $note;
    }
}
