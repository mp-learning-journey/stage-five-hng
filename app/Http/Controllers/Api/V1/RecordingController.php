<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreRecordingRequest;
use App\Models\Recording;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\MockObject\Generator\Exception;

class RecordingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRecordingRequest $request)
    {
        try {
            $upload = FileHelper::upload($request->file, 'videos');
            if ($upload) {
                // save in db
                $
            }
        }
        catch(ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
        catch (Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Oops something went wrong'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Recording $recording)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRecordingRequest $request, Recording $recording)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recording $recording)
    {
        //
    }
}
