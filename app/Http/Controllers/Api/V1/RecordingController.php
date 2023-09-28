<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreRecordingRequest;
use App\Http\Resources\V1\RecordingResource;
use App\Models\Recording;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RecordingController extends Controller
{

    /**
     * @OA\Get(
     *     path="/recordings",
     *     summary="List recordings",
     *     tags={"Recordings"},
     *     description="Get a paginated list of recordings ordered by creation date in descending order.",
     *     operationId="listRecordings",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="url", type="string"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="fileName", type="string"),
     *                     @OA\Property(property="fileSize", type="string"),
     *                     @OA\Property(property="thumbnail", type="string"),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="createdAt", type="string"),
     *                 ),
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string"),
     *                 @OA\Property(property="last", type="string"),
     *                 @OA\Property(property="prev", type="string"),
     *                 @OA\Property(property="next", type="string"),
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Oops, something went wrong",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="statusCode", type="integer"),
     *         ),
     *     ),
     * )
     */
    public function index(): JsonResponse | ResourceCollection
    {
        try{
            $recordings = Recording::orderBy('created_at', 'desc')->paginate();
            return RecordingResource::collection($recordings);
        }
        catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Oops something went wrong', 'statusCode' => 500], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/recordings",
     *     summary="Create a new recording",
     *     tags={"Recordings"},
     *     description="Create a new recording and upload a video file. Returns a response with the newly created recording details on success.",
     *     operationId="createRecording",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     description="The video file to upload",
     *                     type="file",
     *                 ),
     *                 @OA\Property(
     *                     property="title",
     *                     description="The title of the recording (optional)",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     description="A description of the recording (optional)",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="thumbnail",
     *                     description="Thumbnail image for the recording (optional)",
     *                     type="file",
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Recording created successfully",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="id", type="string"),
     *                  @OA\Property(property="title", type="string"),
     *                  @OA\Property(property="url", type="string"),
     *                  @OA\Property(property="description", type="string"),
     *                  @OA\Property(property="fileName", type="string"),
     *                  @OA\Property(property="fileSize", type="string"),
     *                  @OA\Property(property="thumbnail", type="string"),
     *                  @OA\Property(property="slug", type="string"),
     *                  @OA\Property(property="createdAt", type="string"),
     *              ),
     *          ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or file upload failure",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="statusCode",
     *                 type="integer",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Oops, something went wrong",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="statusCode",
     *                 type="integer",
     *             ),
     *         ),
     *     ),
     * )
     */
    public function store(StoreRecordingRequest $request)
    {
        try {
            $upload = FileHelper::upload($request->file, 'videos'); // returns uploaded file name
            if (!$upload) {
                return response()->json(['error' => 'Oops! Could not upload file', 'statusCode' => 422], 422);
            }

            return DB::transaction(function () use ($upload, $request) {
                // save in db
                $recording = new Recording();

                $recording->title = $request->title ?? pathinfo($upload->fileName, PATHINFO_FILENAME); // set filename as title if title does not exist
                $recording->file_location = $upload->file; // file relative path to be stored in DB
                $recording->file_size = $upload->fileSize;
                $recording->file_name = $upload->fileName;
                $recording->slug = $recording->title ? Str::slug($recording->title) : Str::slug($recording->file_name);
                $recording->description = $request->description;
                $thumbnail = $request->hasFile('thumbnail') ? FileHelper::upload($request->thumbnail, 'thumbnails') : null;
                $recording->thumbnail = $thumbnail ? $thumbnail->file : null;
                $recording->save();

                return response()->json([
                    'message' => 'video recording uploaded successfully',
                    'statusCode' => 201,
                    'data' => new RecordingResource($recording)
                ], 201);
            });
        }
        catch(ValidationException $exception) {
            return response()->json(['error' => $exception->validator->errors()->all(), 'statusCode' => 422], 422);
        }
        catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Oops something went wrong', 'statusCode' => 500], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/recordings/{recording}",
     *     summary="Get recording details",
     *     tags={"Recordings"},
     *     description="Retrieve details for a specific recording by its identifier.",
     *     operationId="getRecordingDetails",
     *     @OA\Parameter(
     *         name="recording",
     *         in="path",
     *         description="ID or unique identifier of the recording",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recording details retrieved successfully",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="id", type="string"),
     *                  @OA\Property(property="title", type="string"),
     *                  @OA\Property(property="url", type="string"),
     *                  @OA\Property(property="description", type="string"),
     *                  @OA\Property(property="fileName", type="string"),
     *                  @OA\Property(property="fileSize", type="string"),
     *                  @OA\Property(property="thumbnail", type="string"),
     *                  @OA\Property(property="slug", type="string"),
     *                  @OA\Property(property="createdAt", type="string"),
     *              ),
     *          ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recording not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="statusCode",
     *                 type="integer",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Oops, something went wrong",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="statusCode",
     *                 type="integer",
     *             ),
     *         ),
     *     ),
     * )
     */
    public function show(Recording $recording)
    {
        try{
            if(!$recording->exists()){
                return response()->json(['error' => 'Recording not found', 'statusCode' => 404], 404);
            }
            return new RecordingResource($recording);
        }
        catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Oops something went wrong', 'statusCode' => 500], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/recordings/{recording}",
     *     summary="Delete a recording",
     *     tags={"Recordings"},
     *     description="Delete a specific recording by its identifier. Also, delete the associated file from storage.",
     *     operationId="deleteRecording",
     *     @OA\Parameter(
     *         name="recording",
     *         in="path",
     *         description="ID or unique identifier of the recording to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recording deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="statusCode",
     *                 type="integer",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recording not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="statusCode",
     *                 type="integer",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Oops, something went wrong",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="statusCode",
     *                 type="integer",
     *             ),
     *         ),
     *     ),
     * )
     */
    public function destroy(Recording $recording): \Illuminate\Http\JsonResponse
    {
        try {
            if(!$recording->exists()){
                return response()->json(['error' => 'Recording not found', 'statusCode' => 404], 404);
            }

            if($recording->delete()) {
                // Delete the file from storage
                Storage::delete($recording->file_location);
            }

            return response()->json(['message' => 'Recording deleted successfully', 'statusCode' => 200], 200);
        }
        catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Oops something went wrong', 'statusCode' => 500], 500);
        }
    }
}
