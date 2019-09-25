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
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|alpha',
            'password' => 'required|min:6|alpha_num',
            'emailId' => 'required|email'
        ]);

        if($validator->fails())
        {
            $failedRules = $validator->errors();

            $jsonResponse = [
                'code' => 400,
                'msg' => 'Request cannot be validated!',
                'data' => array()
            ];

            array_push($jsonResponse["data"], $failedRules);

            return response()->json($jsonResponse, 400);
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
            'token' => $userToken->accessToken,
            'data' => 'Registered'
        ];

        return response()->json($jsonResponse, 200);
    }
    
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|alpha_num',
            'emailId' => 'required|email'
        ]);

        if($validator->fails())
        {
            $failedRules = $validator->errors();

            $jsonResponse = [
                'code' => 400,
                'msg' => 'Request cannot be validated!',
                'data' => array()
            ];

            array_push($jsonResponse["data"], $failedRules);

            return response()->json($jsonResponse, 400);
        }

        $userFound = auth()->attempt(['email' => $request->emailId, 'password' => $request->password]);

        if(!$userFound)
        {
            $jsonResponse = [
                'code' => 401,
                'msg' => 'Login Failed!',
                'data' => 'Wrong credentials entered'
            ];

            return response()->json($jsonResponse, 401);
        }

        $user = User::where('email', $request->emailId)->first();
        
        $userToken = $user->createToken('Personal Access Token');

        $jsonResponse = [
            'code' => 200,
            'token' => $userToken->accessToken,
            'msg' => 'Password Matched!'
        ];

        return response()->json($jsonResponse, 200);
    }

    public function resetPassword(Request $request)
    {
        Mail::to('batman@batcave.io')->send(new ResetPassword);

        return view('email');
    }
}

?>