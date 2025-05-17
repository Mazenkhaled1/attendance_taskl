<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\Session;

class AttendanceController extends Controller
{
    public function markAttendance(AttendanceRequest $request)
    {
        $data = $request->validated();
        
        $session = Session::where('qr_code', $request->qr_code)->first();
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code'
            ], 400);
        }
        
        if ($session->isExpired()) {
            Attendance::create([
                'student_id' => $request->student_id,
                'session_id' => $session->id,
                'is_present' => 0
            ]);
            return response()->json([
                'success' => false,
                'message' => 'QR code has expired'
            ], 400);
        }

        $existingAttendance = Attendance::where('student_id', $request->student_id)
            ->where('session_id', $session->id)
            ->first();
            
        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance already marked'
            ], 400);
        }
        
        Attendance::create([
            'student_id' => $request->student_id,
            'session_id' => $session->id,
            'is_present' => 1
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully'
        ]);
    }
}       