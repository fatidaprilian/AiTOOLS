<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function grammar()
    {
        return view('grammar');
    }

    public function upscaleimage()
    {
        return view('upscaling');
    }

    public function textsummarizer()
    {
        return view('text-summarizer');
    }

    public function removebg()
    {
        return view('removebg');
    }

    public function wordtopdf()
    {
        return view('wordtopdf');
    }
}
