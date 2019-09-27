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
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        //dd(encrypt($request->emailId.'|'.time()));

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|alpha',
            'password' => 'required|min:6|alpha_num',
            'emailId' => 'required|email',
            'baseUrl' => 'required'
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

        $user = User::where('email', $request->emailId)->first();

        if($user)
        {
            $jsonResponse = [
                'code' => 409,
                'msg'=> 'User with this Email ID exists!'
            ];

            return response()->json($jsonResponse, 409);
        }

        $hashedPassword = Hash::make($request->password);

        $user = new User;

        $user->name = $request->name;
        $user->password = $hashedPassword;
        $user->email = $request->emailId;

        $user->save();

        $jsonResponse = [
            'code' => 200,
            'data' => 'A verification mail is sent to your Email ID'
        ];

        $baseUrl = $request->baseUrl;
        $verficationToken = encrypt($request->emailId.'|'.time());

        $url = $baseUrl.'/emailverify?emailId='.$request->emailId.'&token='.$verficationToken;

        $msgForMail = 'Click on the following button to verify your Email ID.';

        $buttonText = 'Verify Email';

        Mail::to('tanmay.chaturvedi@gmail.com')->send(new VerificationEmail($msgForMail, $url, $buttonText));

        return response()->json($jsonResponse, 200);
    }

    public function emailVerification(Request $request)
    {        
        $emailId = $request->emailId;

        $decryptedToken = decrypt($request->token);

        if($emailId != substr($decryptedToken, 0, strpos($decryptedToken, '|')))
        {
            $jsonResponse =[
                'code' => 401,
                'error' => 'Invalid EmailI ID!'
            ];

            return response()->json($jsonResponse, 401);
        }

        $token = substr($decryptedToken, (strpos($decryptedToken, '|')+1),strlen ($decryptedToken));

        $tokenCreatedTime = Carbon::createFromTimestamp($token);

        $tokenExpiryTime = Carbon::createFromTimestamp(time())->subHours(2);

        if(!$tokenCreatedTime->gt($tokenExpiryTime))
        {
            $jsonResponse =[
                'code' => 408,
                'error' => 'Request Timeout!'
            ];

            return response()->json($jsonResponse, 408);
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
                'data' => [
                    'msg1' => 'Login Failed!',
                    'msg2' => 'Wrong credentials entered'
                ]
            ];

            return response()->json($jsonResponse, 401);
        }

        $user = User::where('email', $request->emailId)->first();

        if(!$user->email_verified_at)
        {
            $jsonResponse = [
                'code' => 401,
                'data' => [
                    'msg1' => 'Login Failed!',
                    'msg2' => 'Email is not verified yet'
                ]
            ];

            return response()->json($jsonResponse, 401);
        }
        
        $userToken = $user->createToken('Personal Access Token');

        $jsonResponse = [
            'code' => 200,
            'token' => $userToken->accessToken
        ];

        return response()->json($jsonResponse, 200);
    }

   public function generateResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emailId' => 'required|email',
            'baseUrl' => 'required'
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

        $emailId = $request->emailId;
        $baseUrl = $request->baseUrl;
        
        $user = User::where('email', $emailId)->first();

        if($user)
        {
            $deletedRows = PasswordReset::where('email', $emailId)->delete();
            
            $token = sha1(time());
            
            PasswordReset::create([
                'email' => $emailId,
                'token' => $token
            ]);

            $url = $baseUrl.'/reset?token='.$token.'&emailId='.$emailId;

            $msgForMail = 'This email is sent because you requested to reset your password.';

            $buttonText = 'Reset Password';

            Mail::to('tanmay.chaturvedi@gmail.com')->send(new VerificationEmail($msgForMail, $url, $buttonText));
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
            'emailId' => 'required|email',
            'token' => 'required'
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

        $user = User::where('email', $request->emailId)->first();

        if(!$user)
        {
            $jsonResponse = [
                'code' => 401,
                'error' => 'User account not found for the provided Email ID'
            ];

            return response()->json($jsonResponse, 401);
        }

        $passwordReset = PasswordReset::where('email', $request->emailId)->first();

        if(!$passwordReset)
        {
            $jsonResponse = [
                'code' => 401,
                'error' => 'No token has been generated for the provided Email ID'
            ];

            return response()->json($jsonResponse, 401);
        }

        if(!$passwordReset->expired)
        {
            $jsonResponse = [
                'code' => 401,
                'error' => 'Token provided has expired! Please request a new Reset Mail!'
            ];

            return response()->json($jsonResponse, 401);
        }
        
        if($passwordReset->token != $request->token)
        {
            $jsonResponse = [
                'code' => 401,
                'error' => 'An invalid token has been provided! Please request a new Reset Mail!'
            ];

            return response()->json($jsonResponse, 401);
        }
        
        $oldHashedPassword = $user->password;

        if(Hash::check($request->password, $oldHashedPassword))
        {
            $jsonResponse = [
                'code' => 409,
                'error' => 'You entered existing password! Please enter a different one...!'
            ];

            return response()->json($jsonResponse, 409);
        }

        $newHashedPassword = Hash::make($request->password);

        if(!User::where('email', $request->emailId)->update(['password' => $newHashedPassword]))
        {
            $jsonResponse = [
                'code' => 409,
                'error' => 'Internal server error'
            ];

            return response()->json($jsonResponse, 409);
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