<?php

namespace Motor\Media\Database\Seeders;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Motor\Admin\Models\Category;

/**
 * Class MotorMediaDefaultCategorySeeder
 */
class MotorMediaDefaultCategoryTreeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mainCategory = Category::factory()
            ->create([
                'name' => 'Media',
                'scope' => 'media',
            ]);

        Category::factory()
            ->count(3)
            ->state(new Sequence(['parent_id' => $mainCategory->id, 'name' => 'Images', 'scope' => 'media'], [
                'parent_id' => $mainCategory->id,
                'name'      => 'Videos',
                'scope'     => 'media',
            ], [
                'parent_id' => $mainCategory->id,
                'name'      => 'Documents',
                'scope'     => 'media',
            ]))
            ->create();
    }
}
