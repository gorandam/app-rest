<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

use Tymon\JWTAuth\Exceptions\JWTException; // Here we write use statement to import one class from another namespace into current namespace
use JWTAuth; //Here we use JWTAuth facade

class AuthController extends Controller
{
    public function store(Request $request)
    {
      //Here we validate our request data
      $this->validate($request, [
        'name' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:5'
      ]);

      // Here we parse(retrieve) request data
      $name = $request->input('name');//Here we retrieve information for creating our user
      $email = $request->input('email');
      $password = $request->input('password');

      $user = new User([
        'name' => $name,
        'email' => $email,
        'password' => bcrypt($password)
      ]);

      if ($user->save()) {
        $user->signin = [ //Here we create property of user instance but not save it to the database we only add it to instance
            'href' => 'api/v1/user/signin',
            'method' => 'POST',
            'params' => 'email, password'
        ];

        $response = [
              'msg' => 'User created',
              'user' => $user
        ];

        return response()->json($response, 201);
      }

      $response = [
            'msg' =>'An Error occured'
      ];

      return response()->json($response, 404);
    }

    public function signin(Request $request)
    {
      //Here we validate our request data
      $this->validate($request, [
        'email' => 'required|email',
        'password' => 'required'
      ]);

      $credentials = $request->only('email', 'password'); // Here we use only() method on our instance of Request class to get values from email and password keys, and use this $credentials in our login mechanism Here

      //Here we use try catch block to check user credentials and generate and save token in $token
      try {
        if (! $token = JWTAuth::attempt($credentials)) { //Here we check if atuthentication is not succesful and Here we check if credentials are good and retrun and save $token if it is true, false in if logic statement - 
          return response()->json(['msg' => 'Invalid credentials'], 401);
        }

      } catch (JWTException $e) {
        return response()->json(['msg' => 'Could not create token'], 500);
      }

      return response()->json(['token' => $token]);// If we create token and authentiocation is succesful we send back json data with our created token
    }
}
