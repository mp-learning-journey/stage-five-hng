<?php

use App\Http\Controllers\Api\V1\RecordingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('recordings/{id}/chunk', [RecordingController::class, 'store']);
Route::get('recordings', [RecordingController::class, 'index']);
Route::get('recordings/{id}', [RecordingController::class, 'show']);
Route::delete('recordings/{id}', [RecordingController::class, 'destroy']);
