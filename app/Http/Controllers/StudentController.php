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
            'data' => array()
        ];

        foreach($dataFromDB as $data)
        {
            array_push($jsonResponse['data'], $data);
        }

        return response()->json($jsonResponse);

        // return view('/homePage', [
        //     'header' => $tableHeaders,
        //     'dataFromDB' => $dataFromDB
        // ]);
    }
    
    public function insertNewRecord(Request $request)
    {
        $student = new Student;
        
        $student_course = new Student_Course;

        if(empty($request->name) || empty($request->emailId) || empty($request->phoneNo) || empty($request->courses))
        {
            $jsonResponse = [
                'code' => 400,
                'msg' => 'All fields are mandatory!',
                'details' => array()
            ];

            return response()->json($jsonResponse);
        }

        $validatedData = Validator::make($request->all(), [
            'name' => 'required|max:255|alpha',
            'emailId' => 'required|email',
            'phoneNo' => 'required|numeric',
            'courses' => 'required|array'
        ]);

        if($validatedData->fails())
        {
            $failedRules = $validatedData->failed();

            $jsonResponse = [
                'code' => 400,
                'msg' => 'Request cannot be validated!',
                'details' => array()
            ];

            array_push($jsonResponse, $failedRules);

            return response()->json($jsonResponse);
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

        return response()->json($jsonjsonResponse);
    }

    public function getEditId(Request $request, $id)
    {
        if(empty($id))
        {
            return view('/errorPage', [
                'msg' => "No ID found"
            ]);
        }

        return view('/editPage', [
            'id' => $id
        ]);
    }

    public function editRecord(Request $request)
    {
        $student = Student::find($request->id);

        if(empty($student))
        {
            $jsonResponse = [
                'code' => 400,
                'msg' => 'No record found for this ID!',
                'details' => array()
            ];

            return response()->json($jsonResponse);
        }

        if(empty($request->name) || empty($request->emailId) || empty($request->phoneNo) || empty($request->courses))
        {
            $jsonResponse = [
                'code' => 400,
                'msg' => 'All fields are mandatory!',
                'details' => array()
            ];

            return response()->json($jsonResponse);
        }

        $validatedData = Validator::make($request->all(), [
            'name' => 'required|max:255|alpha',
            'emailId' => 'required|email',
            'phoneNo' => 'required|numeric',
            'courses' => 'required|array'
        ]);

        if($validatedData->fails())
        {
            $failedRules = $validatedData->failed();

            $jsonResponse = [
                'code' => 400,
                'msg' => 'Request cannot be validated!',
                'details' => array()
            ];

            array_push($jsonResponse, $failedRules);

            return response()->json($jsonResponse);
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

        return response()->json($jsonjsonResponse);

        // return view('/editPageConfirm', [
        //     'id' => $request->id
        // ]);
    }

    public function confirmDelete(Request $request, $id)
    {
        return view('/deletePageConfirm', [
            'id' => $id
        ]);
    }

    public function deleteRecord(Request $request)
    {
        $student = Student::find($request->id);

        if(empty($student))
        {
            $jsonResponse = [
                'code' => 400,
                'msg' => 'No record found for this ID!',
                'details' => array()
            ];

            return response()->json($jsonResponse);
        }
        
        Student_Course::where('student_id', $request->id)->delete();

        Student::where('id', $request->id)->delete();

        $jsonResponse = [
            'code' => 200,
            'msg' => "Record Deleted",
            'data' => array()
        ];

        array_push($jsonResponse['data'], $request->id);

        return response()->json($jsonResponse);

        // return view('/deletedRecord', [
        //     'id' => $request->id
        // ]);
    }
}
