<?php

namespace Motor\Media\Http\Controllers\Api\V2;

use Motor\Core\Http\Controllers\Api\V2\ApiController;
use Motor\Media\Http\Resources\V2\FileUsageCollection;
use Motor\Media\Models\File;
use Motor\Media\Models\FileAssociation;

/**
 * @tags Files
 */
class FileUsageController extends ApiController
{
    /**
     * List pages and components using this file
     *
     * Returns a deduplicated list of builder pages and custom components
     * that reference this file, grouped by UUID across revisions.
     *
     * @response FileUsageCollection
     */
    public function show(File $file): FileUsageCollection
    {
        $associations = FileAssociation::where('file_id', $file->id)
            ->where('identifier', 'builder_usage')
            ->get();

        if ($associations->isEmpty()) {
            return (new FileUsageCollection(collect()))
                ->additional(['meta' => ['message' => 'File usage retrieved']]);
        }

        // Group associations by model type to batch-query pages and components
        $grouped = $associations->groupBy('model_type');
        $results = collect();

        foreach ($grouped as $modelType => $modelAssociations) {
            $modelIds = $modelAssociations->pluck('model_id')->unique();

            // Collect block_types per model_id for later enrichment
            $blockTypesByModelId = $modelAssociations->groupBy('model_id')
                ->map(fn ($items) => $items
                    ->map(fn ($item) => $item->custom_properties['block_type'] ?? 'other')
                    ->unique()
                    ->values()
                    ->all()
                );

            $models = $modelType::whereIn('id', $modelIds)->get();

            // Group by uuid — each logical page/component appears once
            $byUuid = $models->groupBy('uuid');

            foreach ($byUuid as $uuid => $revisions) {
                // Prefer the current revision, fall back to most recently updated
                $representative = $revisions->firstWhere('is_current', true)
                    ?? $revisions->sortByDesc('updated_at')->first();

                // Merge block_types from all revisions in this uuid group
                $blockTypes = $revisions
                    ->flatMap(fn ($rev) => $blockTypesByModelId->get($rev->id, []))
                    ->unique()
                    ->values()
                    ->all();

                $representative->block_types = $blockTypes;
                $results->push($representative);
            }
        }

        return (new FileUsageCollection($results))
            ->additional(['meta' => ['message' => 'File usage retrieved']]);
    }
}
