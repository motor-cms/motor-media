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
});
