<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffAuthController extends Controller
{
    public function show(): View
    {
        return view('concert.staff-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $expected = (string) config('concert.staff_password');
        if ($expected === '' || ! hash_equals($expected, $data['password'])) {
            return back()->withErrors(['password' => 'Nieprawidłowe hasło.']);
        }

        $request->session()->put('concert_staff_authenticated', true);

        return redirect()->route('concert.check-in');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('concert_staff_authenticated');

        return redirect()->route('concert.staff.login');
    }
}
