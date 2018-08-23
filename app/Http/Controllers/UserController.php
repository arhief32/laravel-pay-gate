<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function hash($string) 
    {
		return hash("sha512", $string . "ceca0623e7992c1620c7372408b6f41d");
	}
    
    public function stringConnection(Request $request)
    {
        $result = DB::connection('schoolgateway')->table('schooldb')->select('*')->where('database',$request->input('SchoolID'))->first();

        return $result;
    }

    public function login(Request $request)
    {
        $username = $request->username;
        $password = $this->hash($request->password);

        $school_id = DB::connection('school-gateway')->table('schooldb')->select('*')->where('database',$request->school_id)->first();
        
        $validate_username = DB::connection('school-gateway')->table($school_id->database.'.student')->select('username','password')
        ->where([['username',$username],['password',$password]])
        ->first();

        return response()->json($validate_username);
    }
}
