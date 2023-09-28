<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreRecordingRequest;
use App\Http\Resources\V1\RecordingResource;
use App\Models\Recording;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RecordingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
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
     * Store a newly created resource in storage.
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
     * Remove the specified resource from storage.
     */
    public function destroy(Recording $recording): \Illuminate\Http\JsonResponse
    {
        try {
            if(!$recording->exists()){
                return response()->json(['error' => 'Recording not found', 'statusCode' => 404], 404);
            }

            $recording->delete();

            return response()->json(['message' => 'Recording deleted successfully', 'statusCode' => 200], 200);
        }
        catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Oops something went wrong', 'statusCode' => 500], 500);
        }
    }
}
