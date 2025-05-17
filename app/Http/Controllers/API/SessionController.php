<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Session;
use Illuminate\Http\Request;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    public function create(Request $request)
    {
 
        $session = new Session();
        $session->qr_code = Str::uuid()->toString();
        $session->expire_at = now()->addMinutes(5);
        $session->save();
    
        $qrSvg = $this->generateQrSvg($session->qr_code);
        
        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'qr_code' => $session->qr_code,
            'qr_svg' => $qrSvg,
            'formatted_expires_at' => $session->expire_at->format('d/m/Y H:i:s')
        ]);
    }
    
    public function refresh($id)
    {
        $session = Session::findOrFail($id);
        $qrCode = $session->generateQrCode();
        $qrSvg = $this->generateQrSvg($qrCode);
        
        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'qr_code' => $qrCode,
            'qr_svg' => $qrSvg,
            'formatted_expires_at' => $session->expire_at->format('d/m/Y H:i:s')
        ]);
    }
    
    private function generateQrSvg($content)
    {
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        
        return base64_encode($writer->writeString($content));
    }
}




