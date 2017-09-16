<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Meeting;// Here we use namespace to import Meeting class;
use App\User;
use JWTAuth;

class RegistrationController extends Controller
{
    public function __construct() // Here we set middleware in our construct funtion to our constructor methods..
    {
      $this->middleware('jwt.auth'); // This middleware requires token to be sent with request
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validation form input
        $this->validate($request, [
          'meeting_id' => 'required',
          'user_id' => 'required'
        ]);

        //We retrieve data from HTTP request
        $meeting_id = $request->input('meeting_id');
        $user_id = $request->input('user_id');

        //Here we fetch meeting or user from database using findOrFail methods and our request data
        $meeting = Meeting::findOrFail($meeting_id);
        $user = User::findOrFail($user_id);

        //Here we create dummy message witch will output default saying  that user is already register for a meeting
        $message = [
            'msg' => 'User is already registered for a meeting',
            'user' => $user,
            'meeting' => $meeting,
            'unregister' => [
                'href' => 'api/v1/meeting/registration/' . $meeting->id,
                'method' => 'DELETE',
            ]
        ];

        // Here we check if user of that ID already exists or registered with this meeting or conected for meeting I fetched here, and we use  $ message in the response
        if ($meeting->users()->where('user_id', $user_id)->first()) {
           return response()->json($message, 404); // Here we use $message in the body of the response
        }
        //Here if the user is not attachd to the meeting crete relations and save it between user model and meeting modle and save it in the pivot table
        $user->meetings()->attach($meeting);

        //Here we create response data for content property(body of our HTTP message)
        $response = [ // Here we build the response in schema MESSAGE - DATA - link
            'msg' => 'User registered for meeting',
            'meeting' => $meeting,
            'user' => $user,
            'unregister' => [
                'href' => 'api/v1/meeting/registration/1',
                'method' => 'DELETE'
            ]
        ];
        //Here we create response object with json data in content property
        return response()->json($response, 201);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      //Here we first fetch meeting from the database and detach users
      $meeting = Meeting::findOrFail($id);

      // Here we parse token and extract user instance from it we do this because only the authenticated (passed jwt.auth middleware) users, and users that send the request can send ID to method
      if (!$user = JWTAuth::parseToken()->authenticate()) { // this will first parse token and then with authenticate() extract authenticated User model instance from token. PS authentication is done in middleware, not here
        return response()->json(['msg' => 'User not found'], 404);
      }
      // Here we check if user is sing up for this meeting
      if(!$meeting->users()->where('user_id', $user->id)->first()) { // Here we check if user->id is ID registered for this meeting. We want only sign up users can delete this meeting
          return response()->json(['msg' => 'User not registered for meeting, unregistration   not successful'], 401);
      };

      //Here we detach() user extracted from token(that send request) and that is registered for a meeting
      $meeting->users()->detach($user->id);

      //Here we create response data for response message that meeting is unregistered
      $response = [
          'msg' => 'User unregistered for meeting',
          'meeting' => $meeting,
          'user' => $user,
          'register' => [
              'href' => 'api/v1/meeting/registration',
              'method' => 'POST',
              'params' => 'user_id, meeting_id'
          ]
      ];

      //Here we create response object with json data in content property
      return response()->json($response, 200);
    }
}
