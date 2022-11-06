<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showForm(): Factory|View|Application
    {
        return view('auth.login');
    }

    public function login(): RedirectResponse
    {
        $attributes = request()->validate([
            'email'=>'required|email',
            'password'=>'required'
        ]);

        if(Auth::attempt($attributes))
        {
            session()->regenerate();
            return redirect()->route('dashboard');
        }
        else{
            return back()->withErrors(['error'=>'Invalid credentials.']);
        }
    }

    public function destroy(): Redirector|Application|RedirectResponse
    {
        Auth::logout();

        return redirect('/login')->with(['success'=>'You\'ve been logged out.']);
    }
}
