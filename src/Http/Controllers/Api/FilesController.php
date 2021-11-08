<?php

namespace Motor\Media\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Motor\Admin\Http\Controllers\ApiController;
use Motor\Media\Http\Requests\Backend\FilePatchRequest;
use Motor\Media\Http\Requests\Backend\FilePostRequest;
use Motor\Media\Http\Resources\FileCollection;
use Motor\Media\Http\Resources\FileResource;
use Motor\Media\Models\File;
use Motor\Media\Services\FileService;

/**
 * Class FilesController
 *
 * @package Motor\Media\Http\Controllers\Api
 */
class FilesController extends ApiController
{
    protected string $model = File::class;

    protected string $modelResource = 'file';

    /**
     * @OA\Get (
     *   tags={"FilesController"},
     *   path="/api/files",
     *   summary="Get files collection",
     *   security={ {"sanctum": {} }},
     *   @OA\Parameter(
     *     @OA\Schema(type="string"),
     *     in="header",
     *     name="Accept",
     *     example="application/json"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/FileResource")
     *       ),
     *       @OA\Property(
     *         property="meta",
     *         ref="#/components/schemas/PaginationMeta"
     *       ),
     *       @OA\Property(
     *         property="links",
     *         ref="#/components/schemas/PaginationLinks"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Collection read"
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response="403",
     *     description="Access denied",
     *     @OA\JsonContent(ref="#/components/schemas/AccessDenied"),
     *   )
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Motor\Media\Http\Resources\FileCollection
     */
    public function index(): FileCollection
    {
        $paginator = FileService::collection()
                                ->getPaginator();

        return (new FileCollection($paginator))->additional(['message' => 'File collection read']);
    }

    /**
     * @OA\Post (
     *   tags={"FilesController"},
     *   path="/api/files",
     *   summary="Create new file",
     *   @OA\RequestBody(
     *     @OA\JsonContent(ref="#/components/schemas/FilePostRequest")
     *   ),
     *   security={ {"sanctum": {} }},
     *   @OA\Parameter(
     *     @OA\Schema(type="string"),
     *     in="header",
     *     name="Accept",
     *     example="application/json"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         ref="#/components/schemas/FileResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="File created"
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response="403",
     *     description="Access denied",
     *     @OA\JsonContent(ref="#/components/schemas/AccessDenied"),
     *   ),
     *   @OA\Response(
     *     response="404",
     *     description="Not found",
     *     @OA\JsonContent(ref="#/components/schemas/NotFound"),
     *   )
     * )
     *
     * Store a newly created resource in storage.
     *
     * @param \Motor\Media\Http\Requests\Backend\FilePostRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(FilePostRequest $request): JsonResponse
    {
        // Check if we have multiple files and instance one new file model per uploaded file
        for ($i = 0; $i < count($request->get('file')); $i++) {
            // Copy request object
            $requestClone = $request->all();
            $requestClone['file'] = $requestClone['file'][$i];
            $result = FileService::create($requestClone)
                                 ->getResult();
        }

        return (new FileResource($result))->additional(['message' => 'File created'])
                                          ->response()
                                          ->setStatusCode(201);
    }

    /**
     * @OA\Get (
     *   tags={"FilesController"},
     *   path="/api/files/{file}",
     *   summary="Get single file",
     *   security={ {"sanctum": {} }},
     *   @OA\Parameter(
     *     @OA\Schema(type="string"),
     *     in="header",
     *     name="Accept",
     *     example="application/json"
     *   ),
     *   @OA\Parameter(
     *     @OA\Schema(type="integer"),
     *     in="path",
     *     name="file",
     *     parameter="file",
     *     description="File id"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         ref="#/components/schemas/FileResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="File read"
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response="403",
     *     description="Access denied",
     *     @OA\JsonContent(ref="#/components/schemas/AccessDenied"),
     *   ),
     *   @OA\Response(
     *     response="404",
     *     description="Not found",
     *     @OA\JsonContent(ref="#/components/schemas/NotFound"),
     *   )
     * )
     *
     * Display the specified resource.
     *
     * @param \Motor\Media\Models\File $record
     * @return \Motor\Media\Http\Resources\FileResource
     */
    public function show(File $record): FileResource
    {
        $result = FileService::show($record)
                             ->getResult();

        return (new FileResource($result))->additional(['message' => 'File read']);
    }

    /**
     * @OA\Put (
     *   tags={"FilesController"},
     *   path="/api/files/{file}",
     *   summary="Update an existing file",
     *   @OA\RequestBody(
     *     @OA\JsonContent(ref="#/components/schemas/FilePatchRequest")
     *   ),
     *   security={ {"sanctum": {} }},
     *   @OA\Parameter(
     *     @OA\Schema(type="string"),
     *     in="header",
     *     name="Accept",
     *     example="application/json"
     *   ),
     *   @OA\Parameter(
     *     @OA\Schema(type="integer"),
     *     in="path",
     *     name="file",
     *     parameter="file",
     *     description="File id"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         ref="#/components/schemas/FileResource"
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="File updated"
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response="403",
     *     description="Access denied",
     *     @OA\JsonContent(ref="#/components/schemas/AccessDenied"),
     *   ),
     *   @OA\Response(
     *     response="404",
     *     description="Not found",
     *     @OA\JsonContent(ref="#/components/schemas/NotFound"),
     *   )
     * )
     *
     * Update the specified resource in storage.
     *
     * @param \Motor\Media\Http\Requests\Backend\FilePatchRequest $request
     * @param \Motor\Media\Models\File $record
     * @return \Motor\Media\Http\Resources\FileResource
     */
    public function update(FilePatchRequest $request, File $record): FileResource
    {
        $result = FileService::update($record, $request)
                             ->getResult();

        return (new FileResource($result))->additional(['message' => 'File updated']);
    }

    /**
     * @OA\Delete (
     *   tags={"FilesController"},
     *   path="/api/files/{file}",
     *   summary="Delete a file",
     *   security={ {"sanctum": {} }},
     *   @OA\Parameter(
     *     @OA\Schema(type="string"),
     *     in="header",
     *     name="Accept",
     *     example="application/json"
     *   ),
     *   @OA\Parameter(
     *     @OA\Schema(type="integer"),
     *     in="path",
     *     name="file",
     *     parameter="file",
     *     description="File id"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="File deleted"
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response="403",
     *     description="Access denied",
     *     @OA\JsonContent(ref="#/components/schemas/AccessDenied"),
     *   ),
     *   @OA\Response(
     *     response="404",
     *     description="Not found",
     *     @OA\JsonContent(ref="#/components/schemas/NotFound"),
     *   ),
     *   @OA\Response(
     *     response="400",
     *     description="Bad request",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Problem deleting file"
     *       )
     *     )
     *   )
     * )
     *
     * Remove the specified resource from storage.
     *
     * @param \Motor\Media\Models\File $record
     * @return \Illuminate\Http\JsonResponse
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
