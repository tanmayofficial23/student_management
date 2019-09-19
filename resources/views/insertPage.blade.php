<html>
    <head>
        <title>Insert Page</title>
        <script src="{{URL::asset('https://code.jquery.com/jquery-3.2.1.min.js')}}"></script>
    </head>

    

    <body>

    <center>

        <h1>Insert a new Record</h1>

        <br/><br/><br/><br/>

        <form action="{{ route('new.insertRecord')}}" method="post">

        {{ csrf_field()}}
        
        Name : <input type="text" id="name" name="name" /><br/><br/>
        Email : <input type="email" id="emailId" name="emailId" /><br/><br/>
        Phone Number : <input type="text" id="phoneNo" name="phoneNo" /><br/><br/>
        Courses : <input type="checkbox" id="course" name="courses[]" value="hindi" />Hindi
        <input type="checkbox" id="course" name="courses[]" value="english" />English
        <input type="checkbox" id="course" name="courses[]" value="maths" />Maths
        <input type="checkbox" id="course" name="courses[]" value="social_studies" />Social Studies
        <input type="checkbox" id="course" name="courses[]" value="physics" />Physics
        <input type="checkbox" id="course" name="courses[]" value="chemistry" />Chemistry
        <input type="checkbox" id="course" name="courses[]" value="biology" />Biology

        <br/><br/>

        <input type="submit" value="Submit Form"/>

        </form>

    </center>

    </body>

</html>