<?php

namespace Motor\Media\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Motor\Admin\Http\Controllers\ApiController;
use Motor\Media\Http\Requests\Backend\FileGetRequest;
use Motor\Media\Http\Requests\Backend\FilePatchRequest;
use Motor\Media\Http\Requests\Backend\FilePostRequest;
use Motor\Media\Http\Resources\FileCollection;
use Motor\Media\Http\Resources\FileResource;
use Motor\Media\Models\File;
use Motor\Media\Services\FileService;

/**
 * Class FilesController
 */
class FilesController extends ApiController
{
    protected string $model = File::class;

    protected string $modelResource = 'file';

    /**
     * List/search all records
     *
     * This will return a paginated response. Some limited search operations are also possible.
     *
     * @response Illuminate\Http\Resources\Json\AnonymousResourceCollection<Illuminate\Pagination\LengthAwarePaginator<FileCollection>>
     */
    public function index(FileGetRequest $request): FileCollection
    {
        $paginator = FileService::collection()
            ->getPaginator();

        return new FileCollection($paginator)->additional(['message' => 'File collection read']);
    }

    /**
     * Create record
     */
    public function store(FilePostRequest $request): JsonResponse
    {
        for ($i = 0; $i < count($request->get('files')); $i++) {
            // Copy request object
            $requestClone = $request->all();
            $requestClone['file'] = $requestClone['files'][$i];
            FileService::create($requestClone)
                ->getResult();
        }
        if (count($request->get('files')) == 0) {
            FileService::create($request)->getResult();
        }

        return response()->json(['message' => 'File created'])->setStatusCode(201);
    }

    /**
     * Get a single record
     */
    public function show(File $record): FileResource
    {
        $result = FileService::show($record)
            ->getResult();

        return new FileResource($result)->additional(['message' => 'File read']);
    }

    /**
     * Update record
     */
    public function update(FilePatchRequest $request, File $record): FileResource
    {
        $result = FileService::update($record, $request)
            ->getResult();

        return new FileResource($result)->additional(['message' => 'File updated']);
    }

    /**
     * Delete record
     */
    public function destroy(File $record): JsonResponse
    {
        $result = FileService::delete($record)
            ->getResult();

        if ($result) {
            return response()->json(['message' => 'File deleted']);
        }

        return response()->json(['message' => 'Problem deleting file'], 404);
    }
}
