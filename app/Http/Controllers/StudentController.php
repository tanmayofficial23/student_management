<?php

namespace App\Http\Controllers;

use App\Student;
use App\Course;
use App\Student_Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;

class StudentController extends Controller
{
    public function showAllRecords()
    {
        $user = auth()->user();

        $dataFromDB = \App\Student::all();

        $tableHeaders = array(
            1 => 'ID',
            2 => 'Name',
            3 => 'Email ID',
            4 => 'Phone Number',
        );

        $jsonResponse = [
            'code' => 200,
            'tableHeaders' => $tableHeaders,
            'data' => array()
        ];

        foreach($dataFromDB as $data)
        {
            array_push($jsonResponse['data'], $data);
        }

        return response()->json($jsonResponse, 200);

    }
    
    public function insertNewRecord(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|alpha',
            'emailId' => 'required|email|unique:students,email_id',
            'phoneNo' => 'required|numeric|digits:10',
            'courses' => 'required|array'
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

        $student = Student::create([
            'name' => $request->name,
            'email_id' => $request->emailId,
            'phone_no' => $request->phoneNo
        ]);

        $coursesSelected = $request->courses;

        $insertedId = $student->id;

        $courseIds = array();

        foreach($coursesSelected as $course)
        {
            $courseId = Course::select('id')->where('name', $course)->get();

            $courseIds[] = $courseId[0]["id"];
        }

        Student::find($insertedId)->courses()->sync($courseIds);

        $jsonResponse = [
            'code' => 200,
            'msg' => 'New record entered!',
            'data' => array()
        ];

        array_push($jsonResponse['data'], $insertedId);
        array_push($jsonResponse['data'], $request->name);
        array_push($jsonResponse['data'], $request->emailId);
        array_push($jsonResponse['data'], $request->phoneNo);
        array_push($jsonResponse['data'], $courseIds);

        return response()->json($jsonResponse, 200);
    }

    public function get(Request $request, $id)
    {
        $student = Student::with('courses')->findOrFail($id);

        if(empty($student))
        {
            $jsonResponse = [
                'code' => 400,
                'data' => 'No record found for this ID!'
            ];

            return response()->json($jsonResponse, 400);
        }

        $coursesSelected = Student_Course::where('student_id', $id)->get()->all();

        $selectedCourses = [];

        foreach($coursesSelected as $course)
        {
            $courses = Course::where('id', $course->course_id)->get();
            
            array_push($selectedCourses, $courses[0]->name);
        }

        $jsonResponse = [
            'code' => 200,
            'msg' => 'Record found!',
            'data' => [
                'studentDetails' => $student,
                'selectedCourses' => $selectedCourses
            ]
        ];

        return response()->json($jsonResponse, 200);
    }

    public function editRecord(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|alpha',
            'emailId' => 'required|email|unique:students,email_id',
            'phoneNo' => 'required|numeric|digits:10',
            'courses' => 'required|array'
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

        if(!Student::where('id', $request->id)->update([ 'name' => $request->name, 'email_id' => $request->emailId, 'phone_no' => $request->phoneNo]))
        {
            $jsonResponse = [
                'code' => 400,
                'error' => 'No record is found for the ID provided.'
            ];

            return response()->json($jsonResponse, 400);
        }

        $coursesSelected = $request->courses;

        $courseIds = array();

        foreach($coursesSelected as $course)
        {
            $courseId = Course::select('id')->where('name', $course)->get();

            $courseIds[] = $courseId[0]["id"];
        }

        Student::find($request->id)->courses()->sync($courseIds);

        $jsonjsonResponse = [
            'code' => 200,
            'msg' => 'Record updated',
            'data' => array()
        ];

        array_push($jsonjsonResponse['data'], $request->id);
        array_push($jsonjsonResponse['data'], $request->name);
        array_push($jsonjsonResponse['data'], $request->emailId);
        array_push($jsonjsonResponse['data'], $request->phoneNo);
        array_push($jsonjsonResponse['data'], $request->courses);

        return response()->json($jsonjsonResponse, 200);

    }

    public function deleteRecord($id)
    {
        $user = auth()->user();
        
        $student = Student::find($id);

        if(empty($student))
        {
            $jsonResponse = [
                'code' => 400,
                'msg' => 'No record found for this ID!'
            ];

            return response()->json($jsonResponse, 400);
        }
        
        Student_Course::where('student_id', $id)->delete();

        Student::where('id', $id)->delete();

        $jsonResponse = [
            'code' => 200,
            'msg' => "Record Deleted",
            'data' => $id
        ];

        return response()->json($jsonResponse, 200);

    }

    public function logout()
    {
        $user = auth()->user();
        
        $token = request()->user()->token();

        $token->revoke();

        $jsonResponse = [
            'code' => 200,
            'data' => 'User has logged off successfully!'
        ];

        return response()->json($jsonResponse, 200);
    }
}
