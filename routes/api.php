<?php

use App\Http\Controllers\API\SessionController;
use App\Http\Controllers\API\AttendanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



// Session routes (for instructors)
Route::post('/sessions', [SessionController::class, 'create']);
Route::post('/sessions/{id}/refresh', [SessionController::class, 'refresh']);

// Attendance routes (for students)
Route::post('/attendance', [AttendanceController::class, 'markAttendance']);

