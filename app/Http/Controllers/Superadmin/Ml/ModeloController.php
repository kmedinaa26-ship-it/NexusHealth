<?php
namespace App\Http\Controllers\Superadmin\Ml;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ModeloController extends Controller
{
    public function index()
    {
        return view('superadmin.ml.modelos');
    }
}
