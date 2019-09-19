<?php

namespace App\Http\Controllers;

use Auth;
use Hash;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|max:255|alpha',
            'password' => 'required|min:6|alpha_num',
            'emailId' => 'required|email'
        ]);

        if($validatedData->fails())
        {
            $failedRules = $validatedData->failed();

            $jsonResponse = [
                'code' => 400,
                'msg' => 'Request cannot be validated!',
                'details' => array()
            ];

            array_push($jsonResponse, $failedRules);

            return response()->json($jsonResponse);
        }

        $hashedPassword = Hash::make($request->password);

        $user = new User;

        $user->name = $request->name;
        $user->password = $hashedPassword;
        $user->email = $request->emailId;

        $user->save();

        $userToken = $user->createToken('Personal Access Token');

        $jsonResponse = [
            'code' => 200,
            'token' => $userToken,
            'msg' => 'Registered'
        ];

        return response()->json($jsonResponse);
    }
    
    public function login(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'password' => 'required|min:6|alpha_num',
            'emailId' => 'required|email'
        ]);

        if($validatedData->fails())
        {
            $failedRules = $validatedData->failed();

            $jsonResponse = [
                'code' => 400,
                'msg' => 'Request cannot be validated!',
                'details' => array()
            ];

            array_push($jsonResponse, $failedRules);

            return response()->json($jsonResponse);
        }

        $user = User::select()->where('email', $request->emailId)->get();

        $user = $user[0];

        if(empty($user))
        {
            $jsonResponse = [
                'code' => 400,
                'msg' => 'No such record exists!'
            ];

            return response()->json($jsonResponse);
        }
        
        $hashedPassword = Hash::make($request->password);

        if(!strcmp($user["password"], $hashedPassword))
        {
            $jsonResponse = [
                'code' => 400,
                'msg' => 'Password didn\'t Matched!'
            ];

            return response()->json($jsonResponse);
        }

        $jsonResponse = [
            'code' => 200,
            'msg' => 'Password Matched!'
        ];

        return response()->json($jsonResponse);
    }
}

?>