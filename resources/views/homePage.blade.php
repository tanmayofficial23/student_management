<html>

    <head>
        <title>Student Management System</title>

        <link rel="stylesheet" href="{{URL::asset('css/cssHomePage.css')}}">

        <script src="{{URL::asset('https://code.jquery.com/jquery-3.2.1.min.js')}}"></script>
        <script type="text/javascipt" src="{{URL::asset('/js/jsHomePage.js')}}"></script>

        <style>
            table, th, td {
                border: 1px solid black;
            }

        </style>

        <script>

            $(document).ready(function(){
                alert("Hello");
            });

        </script>
        
    </head>

    <body>
        <center>
        
            <h1>Student Management System</h1>

            <br/><br/><br/>
            <table>

                <tr>
                    @foreach($header as $heading)
                        
                        <td><?= $heading; ?></td>
                    
                    @endforeach

                    <td>Action</td>

                </tr>

                @foreach($allStudents as $student)

                    <?php
                        $id = $student->id;
                    ?>

                    <tr>
                        <td>{{ $student->id }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->email_id }}</td>
                        <td>{{ $student->phone_no }}</td>
                        <td>
                            <a href="{{ url('/'.$id.'/edit') }}">Edit</a>
                            <a href="{{ url('/'.$id.'/delete') }}" id="deleteRecord">Delete</a>
                        </td>

                    </tr>
                @endforeach

            </table>

            <a href="{{url('/new')}}">Insert a new Record</a>

        </center>

    </body>

<html>