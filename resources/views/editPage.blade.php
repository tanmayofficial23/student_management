<html>

    <head>
        <title>Edit Page</title>
    </head>

    <body>

        <center>

        <h1>Edit Details</h1>

        <br/><br/><br/>

        <h3>Edit Record for ID : <?= $id ?></h3>
        
        <br/>

        <form action="{{ route('edit.editRecord') }}" method="post">

        {{ csrf_field()}}

        <input type="hidden" id="id" name="id" value="10" />
        Name : <input type="text" id="name" name="name" /><br/><br/>
        Email : <input type="email" id="emailId" name="emailId" /><br/><br/>
        Phone Number : <input type="text" id="phoneNo" name="phoneNo" /><br/><br/>
        Courses : <input type="checkbox" id="course[]" name="courses[]" value="hindi" />Hindi
        <input type="checkbox" id="course[]" name="courses[]" value="english" />English
        <input type="checkbox" id="course[]" name="courses[]" value="maths" />Maths
        <input type="checkbox" id="course[]" name="courses[]" value="social_studies" />Social Studies
        <input type="checkbox" id="course[]" name="courses[]" value="physics" />Physics
        <input type="checkbox" id="course[]" name="courses[]" value="chemistry" />Chemistry
        <input type="checkbox" id="course[]" name="courses[]" value="biology" />Biology

        <br/><br/>

        <input type="submit" value="Submit Form"/>

        </form>

        </center>

    </body>

</html>