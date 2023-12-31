<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreRecordingRequest;
use App\Http\Resources\V1\RecordingResource;
use App\Jobs\TranscribeVideo;
use App\Models\Recording;
use FFMpeg\FFMpeg;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Schema(
 *      schema="RecordingResource",
 *      @OA\Property(property="id", type="string", format="uuid", example="9a3e8d15-b805-4309-989b-bba4f78a9248"),
 *      @OA\Property(property="title", type="string", example="Kizz Daniel - Jaho (Official Video)"),
 *      @OA\Property(property="url", type="string", format="uri", example="http://localhost:8000/storage/videos/1695934456_kizz_daniel_-_jaho_(official_video).mp4"),
 *      @OA\Property(property="transcription", type="object", nullable=true, example={"full": "Transcription Text", "segment": {
 *          {
 *              "id": 1,
 *              "position": 1,
 *              "start": "00:00:00",
 *              "endTime": "00:00:10",
 *              "transcription": "Segment 1"
 *          },
 *          {
 *              "id": 2,
 *              "position": 2,
 *              "start": "00:00:10",
 *              "endTime": "00:00:20",
 *              "transcription": "Segment 2"
 *          }
 *      }}),
 *      @OA\Property(property="fileName", type="string", example="Kizz Daniel - Jaho (Official Video).mp4"),
 *      @OA\Property(property="fileSize", type="string", example="10690611"),
 *      @OA\Property(property="thumbnail", type="string", format="uri", nullable=true, example=null),
 *      @OA\Property(property="slug", type="string", example="kizz-daniel-jaho-official-video"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", example="2023-09-28T20:54:16.000000Z")
 *  ),
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
     *                     "transcription": {
     *                         "full": "Transcription Text",
     *                         "segment": {}
     *                     },
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
     *                     "transcription": {
     *                         "full": "Transcription Text",
     *                         "segment": {
     *                             {
     *                                 "id": 1,
     *                                 "position": 1,
     *                                 "start": "00:00:00",
     *                                 "endTime": "00:00:10",
     *                                 "transcription": "Segment 1"
     *                             },
     *                             {
     *                                 "id": 2,
     *                                 "position": 2,
     *                                 "start": "00:10",
     *                                 "endTime": "00:20",
     *                                 "transcription": "Segment 2"
     *                             }
     *                         }
     *                     },
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
     *                     "transcription": {
     *                         "full": "Transcription Text",
     *                         "segment": {
     *                             {
     *                                 "id": 3,
     *                                 "position": 3,
     *                                 "start": "00:00",
     *                                 "endTime": "00:15",
     *                                 "transcription": "Segment 3"
     *                             }
     *                         }
     *                     },
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
     *     path="/api/v1/recordings/{id}/chunk",
     *     summary="Upload a video recording chunk",
     *     tags={"Recordings"},
     *     operationId="uploadRecordingChunk",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Unique identifier for the recording",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="isLastChunk",
     *                     type="boolean",
     *                     description="Set to true if this is the last chunk of the video"
     *                 ),
     *                 @OA\Property(
     *                     property="file",
     *                     type="file",
     *                     format="binary",
     *                     description="The video chunk file to upload (supported formats: mp4, avi, wmv, webm)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Video chunk uploaded successfully"
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Video recording uploaded successfully",
     *         @OA\JsonContent(ref="#/components/schemas/RecordingResource")
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation error or failed to upload file",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="statusCode", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="statusCode", type="integer")
     *         )
     *     )
     * )
     */
    public function store($id, Request $request)
    {
        $request->validate([
                'isLastChunk' => ['required'],
                'file' => ['file','mimes:mp4,avi,wmv,webm', 'max:50480', 'required'],
        ]);

//        try {
            $upload = FileHelper::upload($request, 'videos', $id); // returns uploaded file name
            if (!$upload) {
                return response()->json(['error' => 'Oops! Could not upload file', 'statusCode' => 422], 422);
            }

            if(!$upload->completed){
                return response()->json([
                    'message' => 'uploading chunck...',
                    'statusCode' => 200,
                ], 200);
            }

            return DB::transaction(function () use ($upload, $request) {
                // save in db
                $recording = new Recording();

                $recording->title = $request->title ?? pathinfo($upload->fileName, PATHINFO_FILENAME); // set filename as title if title does not exist
                $recording->file_location = $upload->file; // file relative path to be stored in DB
                $recording->file_size = $upload->fileSize;
                $recording->file_name = $upload->fileName;
                $recording->slug = $recording->title ? Str::slug($recording->title) : Str::slug($recording->file_name);

//                $thumbnail = FileHelper::generateThumbnail(storage_path('app/public/'. $upload->fileName));
//
//                $recording->thumbnail = $thumbnail ? $thumbnail->file : null;
                $recording->save();

                if(TranscribeVideo::dispatch($recording)->onConnection('rabbitmq')){
                    echo "transcribed";
                }else{
                    echo "Not Transcribed";
                }

                return response()->json([
                    'message' => 'video recording uploaded successfully',
                    'statusCode' => 201,
                    'data' => new RecordingResource($recording)
                ], 201);
            });
//        }
//        catch(ValidationException $exception) {
//            return response()->json(['error' => $exception->validator->errors()->all(), 'statusCode' => 422], 422);
//        }
//        catch (\Exception $e) {
//            Log::error($e);
//            return response()->json(['error' => 'Oops something went wrong', 'statusCode' => 500], 500);
//        }
    }

    public function test() {

        $fileName = "videos/video1.webm";
        $tempFile = "videos/gdu3huj.webm";
        $outputPath = storage_path('app/public/' . $fileName);
        $video2 = storage_path('app/public/'. $tempFile);
        $outputPath2 = storage_path('app/public/videos/video5.webm');

        // Initialize FFmpeg
        $ffmpeg = FFMpeg::create();

        // Open the first video file
        $existingVideo = $ffmpeg->open($outputPath);

        if (file_exists($outputPath2)) {
            unlink($outputPath2);
        }
        // Concatenate the videos
        $existingVideo->concat([$outputPath, $video2])->saveFromSameCodecs($outputPath2);

        return true;
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
