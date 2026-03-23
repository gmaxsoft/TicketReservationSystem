<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Tickets\TicketCheckInService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScannerFulfillController extends Controller
{
    public function __invoke(Request $request, TicketCheckInService $checkIn): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:128'],
        ]);

        $result = $checkIn->attempt($data['code']);

        return response()->json([
            'ok' => $result['ok'],
            'message' => $result['message'],
        ], $result['ok'] ? 200 : 422);
    }
}
