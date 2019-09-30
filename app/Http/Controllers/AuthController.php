<?php

namespace App\Http\Controllers;

use Auth;
use Hash;
use App\User;
use App\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
use App\Mail\PasswordResetMail;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|regex:/^[a-zA-Z0-9]+\s*[a-zA-Z0-9]+$/u',
            'password' => 'required|min:6|regex:/^[a-zA-Z0-9\-]*[!@#$%&*]*[a-zA-Z0-9\-]*$/u',
            'emailId' => 'required|email|unique:users,email'
        ]);

        if($validator->fails())
        {
            $failedRules = $validator->errors();

            $jsonResponse = [
                'code' => 422,
                'msg' => 'Request cannot be validated!',
                'data' => array()
            ];

            array_push($jsonResponse["data"], $failedRules);

            return response()->json($jsonResponse, 422);
        }

        $user = User::where('email', $request->emailId)->first();

        if($user)
        {
            $jsonResponse = [
                'code' => 400,
                'msg'=> 'User with this Email ID exists!'
            ];

            return response()->json($jsonResponse, 400);
        }

        $hashedPassword = Hash::make($request->password);

        $user = User::create([
            'name' => $request->name,
            'password' => $hashedPassword,
            'email' => $request->emailId
        ]);

        $jsonResponse = [
            'code' => 200,
            'data' => 'A verification mail is sent to your Email ID'
        ];
        
        $token = encrypt($request->emailId.'|'.time());

        Mail::to($request->emailId)->send(new VerificationEmail($token));

        return response()->json($jsonResponse, 200);
    }

    public function emailVerification(Request $request)
    {        
        $decryptedToken = decrypt($request->token);

        $emailId = explode('|', $decryptedToken)[0];

        if($emailId != substr($decryptedToken, 0, strpos($decryptedToken, '|')))
        {
            $jsonResponse =[
                'code' => 400,
                'error' => 'Invalid Email ID!'
            ];

            return response()->json($jsonResponse, 400);
        }

        $token = explode('|', $decryptedToken)[1];

        $tokenCreatedTime = Carbon::createFromTimestamp($token);

        $tokenExpiryTime = Carbon::createFromTimestamp(time())->subHours(2);

        if(!$tokenCreatedTime->gt($tokenExpiryTime))
        {
            $jsonResponse =[
                'code' => 403,
                'error' => 'Request Timeout!'
            ];

            return response()->json($jsonResponse, 403);
        }

        User::where('email', $emailId)->update(['email_verified_at' => Carbon::now()]);

        $jsonResponse = [
            'code' => 200,
            'data' => 'Email Verified! Continue to login'
        ];

        return response()->json($jsonResponse, 200);

    }
    
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|regex:/^[a-zA-Z0-9\-]*[!@#$%&*]*[a-zA-Z0-9\-]*$/u',
            'emailId' => 'required|email'
        ]);

        if($validator->fails())
        {
            $failedRules = $validator->errors();

            $jsonResponse = [
                'code' => 422,
                'msg' => 'Request cannot be validated!',
                'data' => array()
            ];

            array_push($jsonResponse["data"], $failedRules);

            return response()->json($jsonResponse, 422);
        }

        $user = User::where('email', $request->emailId)->first();

        if(!$user->email_verified_at)
        {
            $jsonResponse = [
                'code' => 400,
                'data' => [
                    'msg1' => 'Login Failed!',
                    'msg2' => 'Email is not verified yet'
                ]
            ];

            return response()->json($jsonResponse, 400);
        }

        $userFound = auth()->attempt(['email' => $request->emailId, 'password' => $request->password]);

        if(!$userFound)
        {
            $jsonResponse = [
                'code' => 400,
                'data' => [
                    'msg1' => 'Login Failed!',
                    'msg2' => 'Wrong credentials entered'
                ]
            ];

            return response()->json($jsonResponse, 400);
        }

        $userToken = $user->createToken('Personal Access Token');

        $jsonResponse = [
            'code' => 200,
            'token' => $userToken->accessToken
        ];

        return response()->json($jsonResponse, 200);
    }

   public function generateResetPasswordLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emailId' => 'required|email',
        ]);

        if($validator->fails())
        {
            $failedRules = $validator->errors();

            $jsonResponse = [
                'code' => 422,
                'msg' => 'Request cannot be validated!',
                'data' => array()
            ];

            array_push($jsonResponse["data"], $failedRules);

            return response()->json($jsonResponse, 422);
        }

        $emailId = $request->emailId;
        
        $user = User::where('email', $emailId)->first();

        if($user)
        {
            $deletedRows = PasswordReset::where('email', $emailId)->delete();
            
            $token = encrypt($request->emailId.'|'.time());

            PasswordReset::create([
                'email' => $emailId,
                'token' => $token
            ]);

            Mail::to($emailId)->send(new PasswordResetMail($token));
        }        

        $jsonResponse = [
            'code' => 200,
            'msg' => 'A mail with reset link will be sent to your Email ID!',
            'data' => $request->emailId
        ];
        
        return response()->json($jsonResponse, 200);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|alpha_num',
            'token' => 'required'
        ]);

        if($validator->fails())
        {
            $failedRules = $validator->errors();

            $jsonResponse = [
                'code' => 422,
                'msg' => 'Request cannot be validated!',
                'data' => array()
            ];

            array_push($jsonResponse["data"], $failedRules);

            return response()->json($jsonResponse, 422);
        }

        $decryptedToken = decrypt($request->token);

        $emailId = explode('|', $decryptedToken)[0];
        $token = explode('|', $decryptedToken)[1];

        $user = User::where('email', $emailId)->first();

        if(!$user)
        {
            $jsonResponse = [
                'code' => 400,
                'error' => 'User account not found for the provided Email ID'
            ];

            return response()->json($jsonResponse, 400);
        }

        $passwordReset = PasswordReset::where('email', $emailId)->first();

        if(!$passwordReset)
        {
            $jsonResponse = [
                'code' => 400,
                'error' => 'No token has been generated for the provided Email ID'
            ];

            return response()->json($jsonResponse, 400);
        }

        if(!$passwordReset->expired)
        {

            $jsonResponse = [
                'code' => 400,
                'error' => 'Token provided has expired! Please request a new Reset Mail!'
            ];

            return response()->json($jsonResponse, 400);
        }
        
        if($passwordReset->token != $request->token)
        {
            $jsonResponse = [
                'code' => 400,
                'error' => 'An invalid token has been provided! Please request a new Reset Mail!'
            ];

            return response()->json($jsonResponse, 400);
        }
        
        $oldHashedPassword = $user->password;

        if(Hash::check($request->password, $oldHashedPassword))
        {
            $jsonResponse = [
                'code' => 400,
                'error' => 'You entered existing password! Please enter a different one...!'
            ];

            return response()->json($jsonResponse, 400);
        }

        $newHashedPassword = Hash::make($request->password);

        if(!User::where('email', $emailId)->update(['password' => $newHashedPassword]))
        {
            $jsonResponse = [
                'code' => 400,
                'error' => 'Record couldn\'t be updated.'
            ];

            return response()->json($jsonResponse, 400);
        }

        PasswordReset::where('email', $request->emailId)->delete();
        
        $jsonResponse = [
            'code' => 200,
            'data' => 'Password has been updated! Login with new password!'
        ];
        
        return response()->json($jsonResponse, 200);
    }
}

?>