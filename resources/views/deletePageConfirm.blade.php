<html>

    <head>
        <title>Confirm deletion</title>
    </head>

    <body>

        <center>

        <h3>Confirm deletion of record with ID : <?= $id ?></h3>

        <br/><br/><br/>

        <form action="{{ route('delete.deleteRecord') }}" method="post">

        {{ csrf_field() }}

        <input type="hidden" id="id" name="id" value="<?= $id ?>" />
        <input type="submit" value="Confirm Delete" />
        </form>

        </center>

    </body>

</html>