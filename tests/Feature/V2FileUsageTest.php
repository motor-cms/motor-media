<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Motor\Builder\Models\BuilderPage;
use Motor\Media\Models\File;
use Motor\Media\Models\FileAssociation;

pest()->group('V2FileUsage')->use(RefreshDatabase::class);

describe('V2 File Usage API', function () {

    it('returns empty data when file has no usage', function () {
        $file = File::first();

        $response = $this->asAdmin()->getJson("/api/v2/files/{$file->id}/usage");

        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2')
            ->assertJsonPath('meta.message', 'File usage retrieved')
            ->assertJsonCount(0, 'data');
    });

    it('returns pages that use a file', function () {
        $file = File::first();
        $page = BuilderPage::where('is_current', true)->first();

        if (! $page) {
            $this->markTestSkipped('No builder pages in seeded data');
        }

        // Create file associations manually
        FileAssociation::create([
            'file_id' => $file->id,
            'model_type' => BuilderPage::class,
            'model_id' => $page->id,
            'identifier' => 'builder_usage',
            'custom_properties' => [
                'atom_uuid' => 'test-atom-1',
                'block_type' => 'image',
            ],
        ]);

        $response = $this->asAdmin()->getJson("/api/v2/files/{$file->id}/usage");

        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2')
            ->assertJsonCount(1, 'data')
            ->assertJson(fn (AssertableJson $json) => $json->has(
                'data.0',
                fn (AssertableJson $data) => $data
                    ->has('id')
                    ->has('uuid')
                    ->has('name')
                    ->has('is_published')
                    ->has('block_types')
                    ->etc()
            )->etc());
    });

    it('groups results by uuid across revisions', function () {
        $file = File::first();

        // Create two "revisions" of the same page (same uuid, different ids)
        $page1 = BuilderPage::factory()->create([
            'uuid' => 'shared-uuid-test',
            'name' => 'Test Page',
            'is_current' => false,
            'is_published' => false,
        ]);
        $page2 = BuilderPage::factory()->create([
            'uuid' => 'shared-uuid-test',
            'name' => 'Test Page',
            'is_current' => true,
            'is_published' => true,
        ]);

        // Both revisions reference the same file
        FileAssociation::create([
            'file_id' => $file->id,
            'model_type' => BuilderPage::class,
            'model_id' => $page1->id,
            'identifier' => 'builder_usage',
            'custom_properties' => ['atom_uuid' => 'atom-old', 'block_type' => 'image'],
        ]);
        FileAssociation::create([
            'file_id' => $file->id,
            'model_type' => BuilderPage::class,
            'model_id' => $page2->id,
            'identifier' => 'builder_usage',
            'custom_properties' => ['atom_uuid' => 'atom-new', 'block_type' => 'image'],
        ]);

        $response = $this->asAdmin()->getJson("/api/v2/files/{$file->id}/usage");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data'); // Grouped by uuid — only one entry

        $data = $response->json('data.0');
        expect($data['uuid'])->toBe('shared-uuid-test');
        expect($data['is_published'])->toBeTrue(); // Prefers current revision
    });

    it('requires authentication', function () {
        $file = File::first();

        $response = $this->getJson("/api/v2/files/{$file->id}/usage");

        $response->assertStatus(401);
    });

    it('returns 404 for non-existent file', function () {
        $response = $this->asAdmin()->getJson('/api/v2/files/999999999/usage');

        $response->assertStatus(404);
    });
});
