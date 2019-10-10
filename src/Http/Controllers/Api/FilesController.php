<?php

namespace Motor\Media\Http\Controllers\Api;

use Motor\Backend\Http\Controllers\Controller;
use Motor\Media\Models\File;
use Motor\Media\Http\Requests\Backend\FileRequest;
use Motor\Media\Services\FileService;
use Motor\Media\Transformers\FileTransformer;

/**
 * Class FilesController
 * @package Motor\Media\Http\Controllers\Api
 */
class FilesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $paginator = FileService::collection()->getPaginator();
        $resource  = $this->transformPaginator($paginator, FileTransformer::class);

        return $this->respondWithJson('File collection read', $resource);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param FileRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(FileRequest $request)
    {
        $result   = FileService::create($request)->getResult();
        $resource = $this->transformItem($result, FileTransformer::class);

        return $this->respondWithJson('File created', $resource);
    }


    /**
     * Display the specified resource.
     *
     * @param File $record
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(File $record)
    {
        $result   = FileService::show($record)->getResult();
        $resource = $this->transformItem($result, FileTransformer::class);

        return $this->respondWithJson('File read', $resource);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param FileRequest $request
     * @param File        $record
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(FileRequest $request, File $record)
    {
        $result   = FileService::update($record, $request)->getResult();
        $resource = $this->transformItem($result, FileTransformer::class);

        return $this->respondWithJson('File updated', $resource);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param File $record
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(File $record)
    {
        $result = FileService::delete($record)->getResult();

        if ($result) {
            return $this->respondWithJson('File deleted', [ 'success' => true ]);
        }

        return $this->respondWithJson('File NOT deleted', [ 'success' => false ]);
    }
}
