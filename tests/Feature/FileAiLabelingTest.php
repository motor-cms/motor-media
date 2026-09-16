<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Motor\Admin\Models\Category;
use Motor\Media\Models\File;

pest()
    ->group('File')
    ->use(RefreshDatabase::class);

function aiLabelingPayload(array $overrides = []): array
{
    return array_merge([
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
    ], $overrides);
}

describe('File ai_labeling', function () {

    it('has an ai_labeling column that defaults to null', function () {
        expect(Schema::hasColumn('files', 'ai_labeling'))->toBeTrue();

        $file = File::factory()->create();

        expect($file->fresh()->ai_labeling)->toBeNull();
    });

    it('stores generated and modified through the API', function (string $value) {
        $response = $this->asAdmin()
            ->postJson('/api/v2/files', aiLabelingPayload(['ai_labeling' => $value]));

        $response->assertStatus(201);
        expect(File::latest('id')->first()->ai_labeling)->toBe($value);
    })->with(['generated', 'modified']);

    it('allows a null ai_labeling', function () {
        $response = $this->asAdmin()
            ->postJson('/api/v2/files', aiLabelingPayload(['ai_labeling' => null]));

        $response->assertStatus(201);
        expect(File::latest('id')->first()->ai_labeling)->toBeNull();
    });

    it('rejects an unknown ai_labeling value on create', function () {
        $response = $this->asAdmin()
            ->postJson('/api/v2/files', aiLabelingPayload(['ai_labeling' => 'invented']));

        $response->assertStatus(422);
    });

    it('rejects an unknown ai_labeling value on update', function () {
        $file = File::first();

        $payload = aiLabelingPayload(['ai_labeling' => 'invented']);
        unset($payload['files']);

        $response = $this->asAdmin()
            ->patchJson('/api/v2/files/'.$file->id, $payload);

        $response->assertStatus(422);
    });

    it('exposes ai_labeling in the V2 list the media picker reads', function () {
        $this->asAdmin()
            ->postJson('/api/v2/files', aiLabelingPayload(['ai_labeling' => 'generated']))
            ->assertStatus(201);

        $created = File::latest('id')->first();

        $response = $this->asAdmin()
            ->getJson('/api/v2/files?per_page=0')
            ->assertStatus(200);

        $entry = collect($response->json('data'))->firstWhere('id', $created->id);

        expect($entry)->not->toBeNull()
            ->and($entry)->toHaveKey('ai_labeling')
            ->and($entry['ai_labeling'])->toBe('generated');
    });

    it('exposes ai_labeling in the V1 FileResource', function () {
        $file = File::first();
        $file->update(['ai_labeling' => 'generated']);

        $this->asAdmin()
            ->getJson('/api/files/'.$file->id)
            ->assertStatus(200)
            ->assertJsonPath('data.ai_labeling', 'generated');
    });

    it('exposes ai_labeling in the V2 FileResource', function () {
        $file = File::first();
        $file->update(['ai_labeling' => 'modified']);

        $this->asAdmin()
            ->getJson('/api/v2/files/'.$file->id)
            ->assertStatus(200)
            ->assertJsonPath('data.ai_labeling', 'modified');
    });
});
