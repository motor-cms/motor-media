<?php

namespace Motor\Media\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Kra8\Snowflake\Snowflake;
use Motor\Media\Models\File;
use Storage;

/**
 * Class MotorMediaDefaultCategorySeeder
 */
class MotorMediaDefaultFileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if (env('APP_ENV') === 'local') {
            $files = Storage::disk('media')->allFiles();
            Storage::disk('media')->delete($files);
        }

        $files = File::factory()->count(10)->create();
        $imageFile = file_get_contents('https://images.pexels.com/photos/7336640/pexels-photo-7336640.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');

        foreach ($files as $file) {
            $filename = Str::random(10).'.png';
            Storage::disk('media')->put($file->id.'/'.$filename, $imageFile);
            $fileSize = strlen($imageFile);
            DB::table('media')->insert([
                'id' => app(Snowflake::class)->short(),
                'model_type' => "Motor\Media\Models\File",
                'model_id' => $file->id,
                'collection_name' => 'file',
                'name' => $filename,
                'file_name' => $filename,
                'mime_type' => 'image/png',
                'disk' => 'media',
                'size' => $fileSize,
                'manipulations' => '[]',
                'custom_properties' => '[]',
                'responsive_images' => '[]',
                'order_column' => 1,
                'conversions_disk' => 'media',
                'uuid' => Str::uuid(),
                'generated_conversions' => '{"thumb": true, "preview": true}',
            ]);
        }
    }
}
