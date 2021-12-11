<?php
session_start();
$session_id = session_id();
?><!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.88.1">
    <title>Checkout example · Bootstrap v5.1</title>

    <script src="https://cdn.socket.io/4.4.0/socket.io.min.js" integrity="sha384-1fOn6VtTq3PWwfsOrk45LnYcGosJwzMHv+Xh/Jx5303FVOXzEnw0EpLv30mtjmlj" crossorigin="anonymous"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <link href="style.css" rel="stylesheet">

</head>

<body class="bg-light">

    <div class="wrapper fadeInDown">
        <div id="formContent">
            <!-- Tabs Titles -->

            <!-- Icon -->
            <div class="fadeIn first p-3">
                <h3>TASKQUEUE-HTTP<br>TEST LOGIN</h3>
            </div>

            <!-- Login Form -->
            <form id="myForm">
                <input type="text" id="login" class="fadeIn second" name="login" placeholder="login" required>
                <input type="text" id="password" class="fadeIn third" name="login" placeholder="password" required>
                <!-- <input type="submit" class="fadeIn fourth" value="Log In"> -->
                <div class="d-grid gap-2 col-6 mx-auto mb-3">
                    <button id="btn_submit" type="submit" class="btn btn-lg btn-primary">Log In</button>
                    <button id="btn_submit_loading" class="btn btn-lg btn-primary" type="button" disabled style="display:none;">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Loading...
                    </button>     
                </div>
                <input type="hidden" id="session_id" value="<?php echo $session_id; ?>">
            </form>

            <!-- Remind Passowrd -->
            <div id="formFooter">
                login: admin | password: admin
                <hr>
                <div><small id="content"></small></div>
            </div>
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous">
    </script>

    <script src="script.js"></script>

</body>

</html>