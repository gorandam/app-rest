<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Meeting;
use JWTAuth;

class MeetingController extends Controller
{
    public function __construct() // Here we set middleware in our construct funtion to our constructor methods..
    {
      $this->middleware('jwt.auth', ['only' => [ // Here we specify to wich method we want to implement our middleware
        'store', 'update', 'destroy'
      ]]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $meetings = Meeting::all();
      foreach($meetings as $meeting) {
        $meeting->view_meeting = [// Here we want to add view_meeting to the our meating model instances
            'href' => 'api/v1/meeting/' . $meeting->id,
            'method' => 'GET'
        ];
      }
      $response = [
          'msg' => 'List of all Meetings',
          'meetings' => $meetings
      ];
      return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function store(Request $request)
     {
         $this->validate($request, [
             'title' => 'required',
             'description' => 'required',
             'time' => 'required|date_format:YmdHie'
         ]);

         // Here we parse token and extract user instance from it
         if (!$user = JWTAuth::parseToken()->authenticate()) { // this will first parse token and then with authenticate extract authenticated User model instance from token PS authentication is done in middleware, not here
           return response()->json(['msg' => 'User not found'], 404);
         }

         //Here we parse requent data from HTTP request body
         $title = $request->input('title');
         $description = $request->input('description');
         $time = $request->input('time');
         $user_id = $user->id;

         //Here we create new meeting
         $meeting = new Meeting([
             'time' => Carbon::createFromFormat('YmdHie', $time),// Here we use Carbon package from php to set date in YmdHie format
             'title' => $title,
             'description' => $description
         ]);
         if ($meeting->save()) {
             $meeting->users()->attach($user_id);// Here we use attach() method to save related data in the tables in the database;
             $meeting->view_meeting = [
                 'href' => 'api/v1/meeting/' . $meeting->id,
                 'method' => 'GET'
             ];
             $message = [
                 'msg' => 'Meeting created',
                 'meeting' => $meeting
             ];
             return response()->json($message, 201);
         }

         $response = [
             'msg' => 'Error during creating'
         ];

         return response()->json($response, 404);
     }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $meeting = Meeting::with('users')->where('id', $id)->firstOrFail();// Here we use eager loading to create join query in background to retrieve meeting and all associated users
      $meeting->view_meetings = [
              'href' => 'api/v1/meeting',
              'method' => 'GET'
          ];


      $response = [
          'msg' => 'Meeting information',
          'meeting' => $meeting
      ];
      return response()->json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'time' => 'required|date_format:YmdHie'// Here laravel uses buil-in php funcion for format dataes
        ]);

        // Here we parse token and extract user instance from it
        if (!$user = JWTAuth::parseToken()->authenticate()) { // this will first parse token and then with authenticate extract authenticated User model instance from token PS authentication is done in middleware, not here
          return response()->json(['msg' => 'User not found'], 404);
        }

        //Here we retrieve data from body of HTTP request
        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $user->id;


        $meeting = Meeting::with('users')->findOrFail($id);//Here we retrieve Meeting instances with related users using eager loading and use findOrFail method to return error if meeting with  passed $id exists
        if(!$meeting->users()->where('user_id', $user_id)->first()) { // Here we check if user_id passed inside body of HTTP request is ID registered for this meeting. We want only registerd users can update this meeting
            return response()->json(['msg' => 'User not registered for meeting, update not successful'], 401);
        };

        // Here we update data to fetched meeting in the database
        $meeting->time = Carbon::createFromFormat('YmdHie', $time);
        $meeting->title = $title;
        $meeting->description = $description;
        if(!$meeting->update()) { //Here we use if statment and update() method to update data to the database, and if it fail (true) it return error
            return response()->json(['msg' => 'Error during updating'], 404);
        }

        // Here we attaching link to the our meeting instance
        $meeting->view_meeting = [
          'href' => 'api/v1/meeting/' . $meeting->id,
          'method' => 'GET'
        ];
        //Here we construct final json data and save it in $response variable
        $response = [ //Here we construct final json data and save it in $response variable
          'msg' => 'Meeting updated',
          'meeting' => $meeting
        ];
        //Here we construct final response object with json data
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

      $meeting = Meeting::with('users')->findOrFail($id);//Here we retrieve Meeting instances with related users using eager loading and use findOrFail method to return error if meeting with  passed $id exists
      // Here we parse token and extract user instance from it we do this because only the authenticated (passed jwt.auth middleware) users, and users that send the request can send ID to method
      if (!$user = JWTAuth::parseToken()->authenticate()) { // this will first parse token and then with authenticate extract authenticated User model instance from token PS authentication is done in middleware, not here
        return response()->json(['msg' => 'User not found'], 404);
      }
      // Here we check if user is sing up for this meeting
      if(!$meeting->users()->where('user_id', $user->id)->first()) { // Here we check if user->id is ID registered for this meeting. We want only sign up users can delete this meeting
          return response()->json(['msg' => 'User not registered for meeting, update not successful'], 401);
      };
      $users = $meeting->users;// Here we acess users key in relations property array in our Meeting instance
      $meeting->users()->detach(); // Here we want to detach all users for this meeting
      if (!$meeting->delete()) {// here after detaching I try to delete meeting, but if it fails I want to loop through all fetched $users and retached them to the meeting
        foreach($users as $user) {
            $meeting->users()->attach($user);
        }
        return response()->json(['msg' => 'deletion failed'], 404);
      }
      //Here we create response data for response message that meeting is actualy deleted
      $response = [
            'msg' => 'Meeting deleted',
            'create' => [
                'href' => 'api/v1/meeting',
                'method' => 'POST',
                'params' => 'title, description, time'
            ]
        ];

        return response()->json($response, 200);
    }
}
