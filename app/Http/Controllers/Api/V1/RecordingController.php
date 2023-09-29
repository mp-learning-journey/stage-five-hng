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

/**
 * @OA\Schema(
 *     schema="RecordingResource",
 *     @OA\Property(property="id", type="string", format="uuid", example="9a3e8d15-b805-4309-989b-bba4f78a9248"),
 *     @OA\Property(property="title", type="string", example="Kizz Daniel - Jaho (Official Video)"),
 *     @OA\Property(property="url", type="string", format="uri", example="http://localhost:8000/storage/videos/1695934456_kizz_daniel_-_jaho_(official_video).mp4"),
 *     @OA\Property(property="description", type="string", nullable=true, example=null),
 *     @OA\Property(property="fileName", type="string", example="Kizz Daniel - Jaho (Official Video).mp4"),
 *     @OA\Property(property="fileSize", type="string", example="10690611"),
 *     @OA\Property(property="thumbnail", type="string", format="uri", nullable=true, example=null),
 *     @OA\Property(property="slug", type="string", example="kizz-daniel-jaho-official-video"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2023-09-28T20:54:16.000000Z")
 * ),
 * @OA\Schema(
 *      schema="RecordingRequest",
 *      type="object",
 *      description="Recording request data",
 *      @OA\Property(property="title", type="string", example="Sample Title", nullable=true),
 *      @OA\Property(property="description", type="string", example="Description of the recording", nullable=true),
 *      @OA\Property(property="file", type="string", format="binary", description="The video file to upload", nullable=false),
 *      @OA\Property(property="thumbnail", type="string", format="binary", description="[Optional] thumbnail image file to upload", nullable=true)
 *  )
 */

class RecordingController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/v1/recordings",
     *     summary="List all recordings",
     *     description="Returns a list of all recordings.",
     *     tags={"Recordings"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/RecordingResource"),
     *             example={
     *                 {
     *                     "id": "9a3e8d15-b805-4309-989b-bba4f78a9248",
     *                     "title": "Kizz Daniel - Jaho (Official Video)",
     *                     "url": "http://localhost:8000/storage/videos/1695934456_kizz_daniel_-_jaho_(official_video).mp4",
     *                     "description": null,
     *                     "fileName": "Kizz Daniel - Jaho (Official Video).mp4",
     *                     "fileSize": "10690611",
     *                     "thumbnail": null,
     *                     "slug": "kizz-daniel-jaho-official-video",
     *                     "createdAt": "2023-09-28T20:54:16.000000Z"
     *                 },
     *                 {
     *                     "id": "9a3e72a0-70d6-4350-a6b3-4955dac13db2",
     *                     "title": "New Recording",
     *                     "url": "http://localhost:8000/storage/videos/1695930017_kizz_daniel_-_jaho_(official_video).mp4",
     *                     "description": null,
     *                     "fileName": "Kizz Daniel - Jaho (Official Video).mp4",
     *                     "fileSize": "10690611",
     *                     "thumbnail": "http://localhost:8000/storage/thumbnails/1695930017_yos3.png",
     *                     "slug": "new-recording",
     *                     "createdAt": "2023-09-28T19:40:17.000000Z"
     *                 },
     *                 {
     *                     "id": "9a3e6efe-9fd6-4570-9781-d99b1e8eb446",
     *                     "title": "Kizz Daniel - Jaho (Official Video)",
     *                     "url": "http://localhost:8000/storage/videos/1695929408_kizz_daniel_-_jaho_(official_video).mp4",
     *                     "description": null,
     *                     "fileName": "Kizz Daniel - Jaho (Official Video).mp4",
     *                     "fileSize": "10690611",
     *                     "thumbnail": "http://localhost:8000/storage/thumbnails/1695929408_yos3.png",
     *                     "slug": "kizz-daniel-jaho-official-video",
     *                     "createdAt": "2023-09-28T19:30:08.000000Z"
     *                 }
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Oops something went wrong"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
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
     *     description="Creates a new recording.",
     *     tags={"Recordings"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RecordingRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Recording created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Recording created successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=201),
     *             @OA\Property(property="data", ref="#/components/schemas/RecordingResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Validation error message"),
     *             @OA\Property(property="statusCode", type="integer", example=422)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Oops something went wrong"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
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
     *     summary="Get a recording by ID",
     *     description="Returns details of a recording by its ID.",
     *     tags={"Recordings"},
     *     @OA\Parameter(
     *         name="recording",
     *         in="path",
     *         required=true,
     *         description="Recording ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(ref="#/components/schemas/RecordingResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recording not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Recording not found"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Oops something went wrong"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
     * )
     */

    public function show(string $id)
    {
        try{
            $recording = Recording::find($id);
            if(!$recording){
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
     *     path="/api/v1/recordings/{id}",
     *     summary="Delete a recording by ID",
     *     description="Deletes a recording by its ID.",
     *     tags={"Recordings"},
     *     @OA\Parameter(
     *         name="recording",
     *         in="path",
     *         required=true,
     *         description="Recording ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recording deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Recording deleted successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recording not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Recording not found"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Oops something went wrong"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
     * )
     */

    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        $recording = Recording::find($id);
        try {
            if(!$recording){
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
