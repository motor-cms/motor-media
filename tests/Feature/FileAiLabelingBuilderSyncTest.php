<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Motor\Admin\Models\Category;
use Motor\Media\Events\FileUploaded;
use Motor\Media\Models\File;

pest()
    ->group('File')
    ->use(RefreshDatabase::class);

/**
 * The builder keeps a snapshot of the file inside every atom that uses it.
 * Only FileUploaded refreshes that snapshot, so a label-only edit has to
 * dispatch the event just like a description or alt_text edit does.
 */
function aiLabelingUpdatePayload(File $file, array $overrides = []): array
{
    return array_merge([
        'alt_text' => $file->alt_text,
        'description' => $file->description,
        'author' => $file->author,
        'source' => $file->source,
        'is_global' => (bool) $file->is_global,
        'is_excluded_from_search_index' => false,
        'categories' => [Category::whereName('Images')->first()->id],
        'metadata' => [],
    ], $overrides);
}

describe('ai_labeling reaches the builder snapshot', function () {

    it('dispatches FileUploaded when only ai_labeling changed', function () {
        $file = File::first();
        $file->update(['ai_labeling' => 'generated']);

        Event::fake([FileUploaded::class]);

        $this->asAdmin()
            ->patchJson('/api/v2/files/'.$file->id, aiLabelingUpdatePayload($file, ['ai_labeling' => 'modified']))
            ->assertStatus(200);

        expect(File::find($file->id)->ai_labeling)->toBe('modified');
        Event::assertDispatched(FileUploaded::class);
    });

    it('dispatches FileUploaded when ai_labeling is cleared', function () {
        $file = File::first();
        $file->update(['ai_labeling' => 'generated']);

        Event::fake([FileUploaded::class]);

        $this->asAdmin()
            ->patchJson('/api/v2/files/'.$file->id, aiLabelingUpdatePayload($file, ['ai_labeling' => null]))
            ->assertStatus(200);

        expect(File::find($file->id)->ai_labeling)->toBeNull();
        Event::assertDispatched(FileUploaded::class);
    });

    it('does not dispatch FileUploaded when nothing relevant changed', function () {
        $file = File::first();
        $file->update(['ai_labeling' => 'generated']);

        Event::fake([FileUploaded::class]);

        $this->asAdmin()
            ->patchJson('/api/v2/files/'.$file->id, aiLabelingUpdatePayload($file, ['ai_labeling' => 'generated']))
            ->assertStatus(200);

        Event::assertNotDispatched(FileUploaded::class);
    });
});
