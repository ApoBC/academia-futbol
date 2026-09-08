<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        if (auth()->user()->esAdmin()) {
            return redirect()->route('reportes.dashboard');
        }

        return view('dashboard');
    }
}
