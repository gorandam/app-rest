<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function store(Request $request)
    {
      $this->validate($request, [
        'name' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:5'
      ]);

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
      $this->validate($request, [
        'email' => 'required|email',
        'password' => 'required'
      ]);
      $email = $request->input('email');// Here we retrieve email(unique indetification criteria) and password...
      $password = $request->input('password');

      $user = [
            'name' => 'Name',
            'email' => $email,
            'password' => $password
        ];

      $response = [
            'msg' => 'User signed in',
            'user' => $user
        ];

      return response()->json($response, 200);
    }
}
