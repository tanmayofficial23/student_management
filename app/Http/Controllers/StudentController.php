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

        // return view('/homePage', [
        //     'header' => $tableHeaders,
        //     'dataFromDB' => $dataFromDB
        // ]);
    }
    
    public function insertNewRecord(Request $request)
    {
        $user = auth()->user();

        $student = new Student;
        
        $student_course = new Student_Course;

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|alpha',
            'emailId' => 'required|email',
            'phoneNo' => 'required|numeric|digits:10',
            'courses' => 'required|array'
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

        $student->name = $request->name;
        $student->email_id = $request->emailId;
        $student->phone_no = $request->phoneNo;

        $coursesSelected = array($request->courses);

        $student->save();
        $insertedId = $student->id;

        $courseIds = array();

        foreach($coursesSelected[0] as $course)
        {
            $courseId = Course::select('id')->where('name', $course)->get();

            $courseIds[] = $courseId[0]["id"];
        }

        $dataToBeUpdated = array();

        foreach($courseIds as $courseId)
        {
            $student_course::insert([
                'student_id' => $insertedId,
                'course_id' => $courseId
            ]);
        }

        $jsonjsonResponse = [
            'code' => 200,
            'msg' => 'New record entered!',
            'data' => array()
        ];

        array_push($jsonjsonResponse['data'], $insertedId);
        array_push($jsonjsonResponse['data'], $request->name);
        array_push($jsonjsonResponse['data'], $request->emailId);
        array_push($jsonjsonResponse['data'], $request->phoneNo);
        array_push($jsonjsonResponse['data'], $courseIds);

        return response()->json($jsonjsonResponse, 200);
    }

    public function getEditId(Request $request, $id)
    {
        $user = auth()->user();
        
        if(empty($id))
        {
            $jsonResponse = [
                'code' => 400,
                'data' => 'No ID mentioned!'
            ];

            return response()->json($jsonResponse, 400);
        }

        $student = Student::find($id);

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
        $student = Student::find($request->id);

        if(empty($student))
        {
            $jsonResponse = [
                'code' => 400,
                'msg' => 'No record found for this ID!',
                'data' => array()
            ];

            return response()->json($jsonResponse, 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|alpha',
            'emailId' => 'required|email',
            'phoneNo' => 'required|numeric|digits:10',
            'courses' => 'required|array'
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

        $student->name = $request->name;
        $student->email_id = $request->emailId;
        $student->phone_no = $request->phoneNo;

        $student->save();
        $coursesSelected = $request->courses;

        $courseIds = array();

        foreach($coursesSelected as $course)
        {
            $courseId = Course::select('id')->where('name', $course)->get();

            $courseIds[] = $courseId[0]["id"];
        }

        //dd($courseIds);

        foreach($courseIds as $courseId)
        {
            Student_Course::where('student_id', $request->id)->delete();
        }

        foreach($courseIds as $courseId)
        {
            Student_Course::insert([
                'student_id' => $request->id,
                'course_id' => $courseId
            ]);
        }

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

        // return view('/editPageConfirm', [
        //     'id' => $request->id
        // ]);
    }

    public function deleteRecord($id)
    {
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

        // return view('/deletedRecord', [
        //     'id' => $request->id
        // ]);
    }

    public function logout()
    {
        $token = request()->user()->token();

        $token->revoke();

        $jsonResponse = [
            'code' => 200,
            'data' => 'User has logged off successfully!'
        ];

        return response()->json($jsonResponse, 200);
    }
}
