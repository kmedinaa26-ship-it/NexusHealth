<?php
namespace App\Http\Controllers\Superadmin\Finanzas;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IncobrablesController extends Controller
{
    public function index()
    {
        return view('superadmin.finanzas.incobrables');
    }
}
