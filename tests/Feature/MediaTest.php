<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Motor\Admin\Models\Category;
use Motor\Media\Models\File;

pest()
    ->group('File')
    ->use(RefreshDatabase::class);

describe('File', function () {
    it('can get all Files', fn () => assertCrudIndex(
        '/api/files',
        10,
        ['id', 'author', 'source']
    ));

    it('can create a File', fn () => assertCrudCreate(
        '/api/files',
        [
            'alt_text' => 'alttext',
            'author' => 'author',
            'source' => 'https://example.com',
            'is_global' => false,
            'categories' => [Category::whereName('Images')->first()->id],
            'description' => 'An Image',
            'file' => [
                'dataUrl' => 'UDEKMyAzCjEgMSAxCjAgMSAwCjAgMSAwCg==',
                'name' => 'test.pbm',
            ],
            'files' => [
                [
                    'alt_text' => '',
                    'dataUrl' => 'UDEKMyAzCjEgMSAxCjAgMSAwCjAgMSAwCg==',
                    'name' => 'test.pbm',
                    'description' => '',
                ],
            ],
            'is_excluded_from_search_index' => false,
            'metadata' => [],
        ],
        File::class
    ));

    it("can't create a File with invalid Category", fn () => assertCrudValidation(
        '/api/files',
        [
            'alt_text' => 'alttext',
            'author' => 'author',
            'categories' => [0],
            'description' => 'An Image',
            'file' => [
                'dataUrl' => 'UDEKMyAzCjEgMSAxCjAgMSAwCjAgMSAwCg==',
                'name' => 'test.pbm',
            ],
            'files' => [
                [
                    'alt_text' => '',
                    'dataUrl' => 'UDEKMyAzCjEgMSAxCjAgMSAwCjAgMSAwCg==',
                    'name' => 'test.pbm',
                    'description' => '',
                ],
            ],
            'is_excluded_from_search_index' => false,
            'metadata' => [],
        ],
        File::class
    ));

    it("can't create an empty File", fn () => assertCrudValidation(
        '/api/files',
        [],
        File::class
    ));

    it('can get a specific File', fn () => assertCrudShow(
        '/api/files/'.File::first()->id,
        ['id', 'description', 'author', 'source', 'is_global', 'alt_text', 'file', 'categories', 'exists', 'is_excluded_from_search_index', 'tags']
    ));

    it('can update files', fn () => assertCrudUpdate(
        '/api/files/'.File::first()->id,
        [
            'alt_text' => 'alttext',
            'author' => 'changed',
            'source' => 'test source',
            'categories' => [Category::whereName('Images')->first()->id],
            'description' => 'An Image',
            'file' => [
                'dataUrl' => 'UDEKMyAzCjEgMSAxCjAgMSAwCjAgMSAwCg==',
                'name' => 'test.pbm',
            ],
            'files' => [
                [
                    'alt_text' => '',
                    'dataUrl' => 'UDEKMyAzCjEgMSAxCjAgMSAwCjAgMSAwCg==',
                    'name' => 'test.pbm',
                    'description' => '',
                ],
            ],
            'is_excluded_from_search_index' => false,
            'metadata' => [],
        ],
        'author',
        'changed'
    ));

    it('can delete files', fn () => assertCrudDelete(
        '/api/files/'.File::first()->id,
        File::class
    ));

    it("can't do anything without permissions", fn () => assertPermissionsDenied(
        '/api/files',
        File::first()->id
    ));

    // Regression + bucket expansion: `mime_type` lives on the related media row,
    // not the `files` table. The filter is routed through Scout via onlyScout,
    // and short bucket keywords (`image`, `video`, `audio`, `document`) expand
    // to a whereIn over every MIME in that bucket.
    it('aggregates all images when filtering by mime_type=image (v1)', function () {
        $this->asAdmin()
            ->getJson('/api/files?mime_type=image')
            ->assertStatus(200)
            ->assertJsonCount(10, 'data');
    });

    it('aggregates all images when filtering by mime_type=image (v2)', function () {
        $this->asAdmin()
            ->getJson('/api/v2/files?mime_type=image')
            ->assertStatus(200)
            ->assertJsonCount(10, 'data');
    });

    it('matches exact MIME types when given a full mime_type', function () {
        $this->asAdmin()
            ->getJson('/api/files?mime_type=image/png')
            ->assertStatus(200)
            ->assertJsonCount(10, 'data');
    });
});
