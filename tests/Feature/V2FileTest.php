<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Motor\Admin\Models\Category;
use Motor\Media\Models\File;

pest()->group('V2File')->use(RefreshDatabase::class);

describe('V2 File API', function () {

    it('includes api_version v2 in response meta', function () {
        $response = $this->asAdmin()->getJson('/api/v2/files');

        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2');
    });

    it('can get all files', function () {
        $response = $this->asAdmin()->getJson('/api/v2/files');

        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2')
            ->assertJson(fn (AssertableJson $json) => $json->has(
                'data',
                10,
                fn (AssertableJson $data) => $data
                    ->has('id')
                    ->has('author')
                    ->has('source')
                    ->etc()
            )->etc());
    });

    it('can get a specific file', function () {
        $response = $this->asAdmin()
            ->getJson('/api/v2/files/'.File::first()->id);

        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2')
            ->assertJson(fn (AssertableJson $json) => $json->has(
                'data',
                fn (AssertableJson $data) => $data
                    ->has('id')
                    ->has('description')
                    ->has('author')
                    ->has('source')
                    ->has('is_global')
                    ->has('alt_text')
                    ->etc()
            )->etc());
    });

    it('can create a file', function () {
        $fileCount = File::count();

        $response = $this->asAdmin()
            ->post('/api/v2/files', [
                'alt_text' => 'v2 alttext',
                'author' => 'v2 author',
                'source' => 'https://example.com',
                'is_global' => false,
                'categories' => [
                    Category::whereName('Images')->first()->id,
                ],
                'description' => 'A V2 Image',
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
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('meta.api_version', 'v2');
        expect(File::count() - $fileCount)->toBe(1);
    });

    it('validates required fields on create with V2 error envelope', function () {
        $fileCount = File::count();

        $response = $this->asAdmin()
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/v2/files', []);

        $response->assertStatus(422)
            ->assertJsonPath('meta.api_version', 'v2')
            ->assertJsonStructure([
                'error' => ['code', 'message', 'details'],
                'meta' => ['api_version'],
            ]);
        expect(File::count() - $fileCount)->toBe(0);
    });

    it("can't create file with invalid category", function () {
        $fileCount = File::count();

        $response = $this->asAdmin()
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/v2/files', [
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
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('meta.api_version', 'v2');
        expect(File::count() - $fileCount)->toBe(0);
    });

    it('can update a file', function () {
        $response = $this->asAdmin()
            ->put('/api/v2/files/'.File::first()->id, [
                'alt_text' => 'v2 updated alttext',
                'author' => 'v2 updated author',
                'source' => 'v2 test source',
                'categories' => [
                    Category::whereName('Images')->first()->id,
                ],
                'description' => 'A V2 Updated Image',
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
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2')
            ->assertJson(fn (AssertableJson $json) => $json->has(
                'data',
                fn (AssertableJson $data) => $data
                    ->where('author', 'v2 updated author')
                    ->etc()
            )->etc());
    });

    it('can delete a file with 204 No Content', function () {
        $fileCount = File::count();

        $this->asAdmin()
            ->delete('/api/v2/files/'.File::first()->id)
            ->assertStatus(204)
            ->assertNoContent();

        expect($fileCount - File::count())->toBe(1);
    });

    it('denies access to basic users', function () {
        $fileId = File::first()->id;

        $this->asBasic()->getJson('/api/v2/files')->assertStatus(403);
        $this->asBasic()->getJson('/api/v2/files/'.$fileId)->assertStatus(403);
        $this->asBasic()->post('/api/v2/files', [])->assertStatus(403);
        $this->asBasic()->put('/api/v2/files/'.$fileId, [])->assertStatus(403);
        $this->asBasic()->delete('/api/v2/files/'.$fileId)->assertStatus(403);
    });

    it('can filter files by client_id', function () {
        $client = \Motor\Admin\Models\Client::first();

        // Create files with different client_ids
        $matchingFile = File::factory()->create(['client_id' => $client->id]);
        $otherFile = File::factory()->create(['client_id' => null]);

        $response = $this->asAdmin()
            ->getJson('/api/v2/files?client_id='.$client->id);

        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2');

        $returnedIds = collect($response->json('data'))->pluck('id')->all();
        expect($returnedIds)->toContain($matchingFile->id);
        expect($returnedIds)->not->toContain($otherFile->id);
    });

    // Note: category_id filter uses RelationRenderer which works via Meilisearch array filtering
    // Not testable with Scout collection driver (test env) - requires Meilisearch
});
