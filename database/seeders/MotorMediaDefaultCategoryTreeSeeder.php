<?php

namespace Motor\Media\Database\Seeders;

use Illuminate\Database\Seeder;

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
        $node = \Motor\Backend\Models\Category::create([
            'name'       => 'Media',
            'scope'      => 'media',
            'created_by' => 1,
            'updated_by' => 1,

            'children' => [
                [
                    'name'       => 'Images',
                    'scope'      => 'media',
                    'created_by' => 1,
                    'updated_by' => 1,
                ],
                [
                    'name'       => 'Videos',
                    'scope'      => 'media',
                    'created_by' => 1,
                    'updated_by' => 1,
                ],
                [
                    'name'       => 'Documents',
                    'scope'      => 'media',
                    'created_by' => 1,
                    'updated_by' => 1,
                ],
            ],
        ]);
    }
}
