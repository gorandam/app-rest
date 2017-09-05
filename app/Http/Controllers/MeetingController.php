<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function __construct() // Here we set middleware in our construct funtion to our constructor methods..
    {
      //$this->middleware('name');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $meeting = [
          'title' => 'Title',
          'description' => 'Description',
          'time' => 'Time',
          'user_id' => 'User Id',
          'view_meeting' => [
              'href' => 'api/v1/meeting/1',
              'method' => 'GET'
          ]
      ];

      $response = [
          'msg' => 'List of all Meetings',
          'meetings' => [
              $meeting,
              $meeting
          ]
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
            'time' => 'required|date_format:YmdHie',// Here laravel uses buil-in php funcion for format dataes see documentation
            'user_id' => 'required'
        ]);

        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $request->input('user_id');
        $meeting = [
            'title' => $title,
            'description' => $description,
            'time' => $time,
            'user_id' => $user_id,
            'view_meeting' => [
                'href' => 'api/v1/meeting/1',
                'method' => 'GET'
            ]
        ];

        $response = [
            'msg' => 'Meeting created',
            'meeting' => $meeting
        ];

        return response()->json($response, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $meeting = [
          'title' => 'Title',
          'description' => 'Description',
          'time' => 'Time',
          'user_id' => 'User Id',
          'view_meetings' => [
              'href' => 'api/v1/meeting',
              'method' => 'GET'
          ]
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
            'time' => 'required|date_format:YmdHie',// Here laravel uses buil-in php funcion for format dataes
            'user_id' => 'required'
        ]);
        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $request->input('user_id');

        $meeting = [ // This simulate here database data, we dont have database here
          'title' => $title,
          'description' => $description,
          'time' => $time,
          'user_id' => $user_id,
          'view_meeting' => [
            'href' => 'api/v1/meeting/1',
            'method' => 'GET'
          ]
        ];
        $response = [ // This simulate here database data, we don't have database here
          'msg' => 'Meeting updated',
          'meeting' => $meeting
        ];

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
