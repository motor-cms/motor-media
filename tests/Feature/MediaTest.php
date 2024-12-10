<?php

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Support\Facades\Artisan;
use Motor\Media\Models\File;
use Motor\Admin\Models\Category;

describe('File', function () {
    it('can get all Files')
        ->asAdmin()
        ->get('/api/files')
        ->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json->has(
            'data',
            10,
            fn(AssertableJson $data) =>
            $data
                ->has('id')
                ->has('author')
                ->has('source')
                ->etc()
        )->etc());
    it('can create a File', function () {
        $filecount = File::count();
        $this->asAdmin()
            ->post('/api/files', [
                'alt_text' => "alttext",
                'author' => 'author',
                'categories' => [Category::whereName('Images')->first()->id],
                'description' => 'An Image',
                'file' => [
                    'dataUrl' => 'UDEKMyAzCjEgMSAxCjAgMSAwCjAgMSAwCg==',
                    'name' => 'test.pbm',
                ],
                'files' => [
                    [
                        'alt_text' => "",
                        'dataUrl' => 'UDEKMyAzCjEgMSAxCjAgMSAwCjAgMSAwCg==',
                        'name' => 'test.pbm',
                        'description' => '',
                    ]
                ],
                'is_excluded_from_search_index' => false,
                'metadata' => []
            ])->assertStatus(201);
        expect(File::count() - $filecount)->toBe(1);
    });
    it("can't create a File with invalid Category", function () {
        $filecount = File::count();
        $this->asAdmin()->withJsonHeaders()
            ->post('/api/files', [
                'alt_text' => "alttext",
                'author' => 'author',
                'categories' => [0],
                'description' => 'An Image',
                'file' => [
                    'dataUrl' => 'UDEKMyAzCjEgMSAxCjAgMSAwCjAgMSAwCg==',
                    'name' => 'test.pbm',
                ],
                'files' => [
                    [
                        'alt_text' => "",
                        'dataUrl' => 'UDEKMyAzCjEgMSAxCjAgMSAwCjAgMSAwCg==',
                        'name' => 'test.pbm',
                        'description' => '',
                    ]
                ],
                'is_excluded_from_search_index' => false,
                'metadata' => []
            ])->assertStatus(422);
        expect(File::count() - $filecount)->toBe(0);
    });
    /*it("can't create a File with non file Category", function () {
        $filecount = File::count();
        $this->asAdmin()->withJsonHeaders()
            ->post('/api/files', [
                'alt_text' => "alttext",
                'author' => 'author',
                'categories' => [Category::whereName('Pages')->first()->id],
                'description' => 'An Image',
                'file' => [
                    'dataUrl' => 'UDEKMyAzCjEgMSAxCjAgMSAwCjAgMSAwCg==',
                    'name' => 'test.pbm',
                ],
                'files' => [
                    [
                        'alt_text' => "",
                        'dataUrl' => 'UDEKMyAzCjEgMSAxCjAgMSAwCjAgMSAwCg==',
                        'name' => 'test.pbm',
                        'description' => '',
                    ]
                ],
                'is_excluded_from_search_index' => false,
                'metadata' => []
            ])->assertStatus(422);
        expect(File::count() - $filecount)->toBe(0);
    });*/
    it("can't create an empty File", function () {
        $filecount = File::count();
        $this->asAdmin()->withJsonHeaders()
            ->post('/api/files', [])->assertStatus(422);
        expect(File::count() - $filecount)->toBe(0);
    });
    it(
        'can get a specific File',
        fn() =>
        $this->asAdmin()->get('/api/files/' . File::first()->id)
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $json) => $json->has(
                'data',
                fn(AssertableJson $data) =>
                $data
                    ->has('id')
                    ->has('description')
                    ->has('author')
                    ->has('source')
                    ->has('is_global')
                    ->has('alt_text')
                    ->has('file')
                    ->has('categories')
                    ->has('exists')
                    ->has('is_excluded_from_search_index')
                    ->has('tags')
                    ->etc()
            )->etc())
    );
    it('can update files', fn() => $this->asAdmin()
        ->put('/api/files/' . File::first()->id, [
            'alt_text' => "alttext",
            'author' => 'changed',
            'categories' => [Category::whereName('Images')->first()->id],
            'description' => 'An Image',
            'file' => [
                'dataUrl' => 'UDEKMyAzCjEgMSAxCjAgMSAwCjAgMSAwCg==',
                'name' => 'test.pbm',
            ],
            'files' => [
                [
                    'alt_text' => "",
                    'dataUrl' => 'UDEKMyAzCjEgMSAxCjAgMSAwCjAgMSAwCg==',
                    'name' => 'test.pbm',
                    'description' => '',
                ]
            ],
            'is_excluded_from_search_index' => false,
            'metadata' => []
        ])->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json->has('data', fn(AssertableJson $data) =>
        $data->where('author', 'changed')->etc())->etc()));
    it('can delete files', function () {
        $filecount = File::count();
        $this->asAdmin()->delete('/api/files/' . File::whereAuthor('changed')->first()->id)
            ->assertStatus(200);
        expect($filecount - File::count())->toBe(1);
        Artisan::call('migrate:fresh --seed');
    });
    it(
        "can't do anything without permissions",
        function () {
            $this->asBasic()->getJson('/api/files')->assertStatus(403);
            $this->asBasic()->getJson('/api/files/' . File::first()->id)->assertStatus(403);
            $this->asBasic()->post('/api/files', [])->assertStatus(403);
            $this->asBasic()->put('/api/files/' . File::first()->id, [])->assertStatus(403);
            $this->asBasic()->delete('/api/files/' . File::first()->id)->assertStatus(403);
        }
    );
});
