<?php
namespace App\Http\Controllers\Superadmin\Ml;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DriftController extends Controller
{
    public function index()
    {
        return view('superadmin.ml.drift');
    }
}
