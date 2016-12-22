<?php

$factory->define(Motor\Media\Models\File::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word
    ];
});
