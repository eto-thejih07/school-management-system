<?php
if(isset($_POST['submit'])){
    $username = $_POST['username'];
    $password = $_POST['pw'];

    include "db.php";
    session_start();

    $sql = "INSERT INTO users(username, pw) VALUES('$username', '$password')";
    $result = mysqli_query($conn, $sql);
    if(!$result){
        echo "error: {$conn->error}";
    }
    else{
        header("Location: redirect.php");
        }
}
?>

<html>
    <head>
        <title>Admin Registration - B/ Sri Dhammananda Maha Vidyalaya</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Abhaya+Libre:wght@400;500;600;700;800&family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="css/login_style.css">
        <link rel="shortcut icon" type="x-icon" href="css/img/DMVLOGO.png">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
<body>
        <div class="container">
        <div class="logo"><img src="css/img/DMVLOGO.png" alt=""><span class="dmv">B/ Sri Dhammananda Maha Vidyalaya</span>
        </div>
            <div class='title'>Admin Registration Portal</div>
            <div class='content'>
                <form action="admin_reg.php" method="POST">
                <div class="user-details">
                    <div class="input-box">
                        <Span class="details">Username </Span>
                        <input type="text" name="username" placeholder="Username" required>
                    </div>
                    <div class="input-box">
                        <Span class="details">Password </Span>
                        <input type="password" name="pw" placeholder="Password" required>
                    </div>
                    <div class="button">
                        <input type="submit" name="submit" value="Register">
                    </div>
                </div>
            </div>
        </div>


</html>