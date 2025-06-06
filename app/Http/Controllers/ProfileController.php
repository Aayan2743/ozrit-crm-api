<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
class ProfileController extends Controller
{
    //

    public function index(){
     
        $user=Auth()->user();

        return response()->json([
            'status'=>true,
            'data'=>$user
        ]);
     
    }
}
