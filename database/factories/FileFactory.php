<?php

namespace Motor\Media\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Motor\Media\Models\File;

class FileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'description' => $this->faker->sentence,
            'author'        => $this->faker->name,
            'source'        => $this->faker->url,
            'alt_text'      => $this->faker->sentence,
            'is_global'     => $this->faker->boolean,
        ];
    }
}
