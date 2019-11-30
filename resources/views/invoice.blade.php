<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>EPBasic Task</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    </head>
    <body>
        <div class="jumbotron jumbotron-fluid">
            <div class="container">
                <h1 class="display-4">EPBasic Task</h1>
                <p class="lead">PDF generado en {{ $date }} por
                    <b>{{ $user->name }} {{ $user->surname }}</b>.
                </p>
            </div>
        </div>
    </body>
</html>
