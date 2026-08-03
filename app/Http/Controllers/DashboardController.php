<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){
        try{
            return view('dashboard.index');
        }catch(Exception $e){
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
