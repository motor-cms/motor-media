<?php

namespace Motor\Media\Http\Controllers\Api\V2;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Motor\Core\Http\Controllers\Api\V2\ApiController;
use Motor\Media\Http\Requests\Backend\FileGetRequest;
use Motor\Media\Http\Requests\Backend\FilePatchRequest;
use Motor\Media\Http\Requests\Backend\FilePostRequest;
use Motor\Media\Http\Resources\V2\FileCollection;
use Motor\Media\Http\Resources\V2\FileResource;
use Motor\Media\Models\File;
use Motor\Media\Services\FileService;

/**
 * @tags Files
 */
class FilesController extends ApiController
{
    protected string $model = File::class;

    protected string $modelResource = 'file';

    /**
     * @response Illuminate\Http\Resources\Json\AnonymousResourceCollection<Illuminate\Pagination\LengthAwarePaginator<FileResource>>
     */
    public function index(FileGetRequest $request): FileCollection
    {
        $paginator = FileService::collection()->getPaginator();

        return (new FileCollection($paginator))
            ->additional(['meta' => ['message' => 'Files retrieved']]);
    }

    public function store(FilePostRequest $request): JsonResponse
    {
        for ($i = 0; $i < count($request->get('files')); $i++) {
            $requestClone = $request->all();
            $requestClone['file'] = $requestClone['files'][$i];
            FileService::create($requestClone)->getResult();
        }

        if (count($request->get('files')) == 0) {
            FileService::create($request)->getResult();
        }

        return response()->json([
            'meta' => [
                'api_version' => 'v2',
                'message' => 'File created',
            ],
        ], 201);
    }

    public function show(File $file): FileResource
    {
        $result = FileService::show($file)->getResult();

        return (new FileResource($result))
            ->additional(['meta' => ['message' => 'File retrieved']]);
    }

    public function update(FilePatchRequest $request, File $file): FileResource
    {
        $result = FileService::update($file, $request)->getResult();

        return (new FileResource($result))
            ->additional(['meta' => ['message' => 'File updated']]);
    }

    public function destroy(File $file): Response
    {
        $result = FileService::delete($file)->getResult();

        if ($result) {
            return $this->noContentResponse();
        }

        abort(404, 'Problem deleting file');
    }
}
