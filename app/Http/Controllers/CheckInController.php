<?php

namespace App\Http\Controllers;

use App\Services\Tickets\TicketCheckInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckInController extends Controller
{
    public function index(): View
    {
        return view('concert.check-in');
    }

    public function process(Request $request, TicketCheckInService $checkIn): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:128'],
        ]);

        $result = $checkIn->attempt($data['code']);

        if (! $result['ok']) {
            return back()->withErrors(['code' => $result['message']])->withInput();
        }

        return back()->with('status', $result['message']);
    }
}
