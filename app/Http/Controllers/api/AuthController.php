<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        $validations = [
            'email' => 'email|required',
            'password' => 'required'
        ];

        $validator = Validator::make($request->all(), $validations);

        if ($validator->fails())
            return response()->json(['error' => implode(" - ", $validator->errors()->all())], 500);

        $user = User::where('email', $request->email)->first();

        if(!$user){
            return response(['success' => false , 'message' => 'This user does not exist, check your details'], 400);
        }
        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json(['success' => false, 'message' => 'Invalid email or password.'], 401);
        }

        $user = auth()->user();

        $accessToken = $user->createToken('authToken')->plainTextToken;

        return response()->json(['success' => true , 'user' => $user , 'access_token' => $accessToken]);
    }

}
