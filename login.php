<?php
session_start();
require_once("config/db.php");

if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE username=?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($admin = mysqli_fetch_assoc($result)) {

        if ($password == $admin['password']) {

            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['first_name'] . " " . $admin['last_name'];

            header("Location: index.php");
            exit();

        } else {
            $error = "Incorrect password.";
        }

    } else {
        $error = "Username not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<title>LIGER Login</title>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Plus Jakarta Sans',sans-serif;}

body{

margin:0;

height:100vh;

display:flex;

justify-content:center;

align-items:center;

background:
radial-gradient(circle at top,#2d1b51,#171321 70%);

overflow:hidden;

position:relative;

}




h1{

color:#B7FF00;

font-size:55px;

margin-bottom:10px;

}

.subtitle{

color:white;

margin-bottom:35px;

}

input{

width:100%;

padding:15px;

margin-bottom:18px;

background:#4C3478;

border:none;

border-radius:8px;

font-size:15px;

color:white;

}

input::placeholder{

color:#ddd;

}

input:focus{

outline:none;

border:2px solid #B7FF00;

}

button{

width:100%;

padding:15px;

background:#B7FF00;

border:none;

border-radius:8px;

font-size:18px;

font-weight:bold;

cursor:pointer;

transition:.3s;

}

button:hover{

background:#D8FF54;

}

.error{

margin-top:18px;

color:#ff5d5d;

font-weight:bold;

}

.footer{

margin-top:30px;

color:#999;

font-size:13px;

}

body::before{

content:"⚽";

position:absolute;

left:70px;

top:70px;

font-size:220px;

opacity:.03;

animation:floatBall 7s ease-in-out infinite;

}

body::after{

content:"🏆";

position:absolute;

right:90px;

bottom:80px;

font-size:170px;

opacity:.2;

}

body::after{

content:"🏆";

position:absolute;

right:70px;

bottom:60px;

font-size:190px;

color:#B7FF00;

opacity:.08;

pointer-events:none;

}
@keyframes floatBall{

0%{

transform:translateY(0);

}

50%{

transform:translateY(-25px);

}

100%{

transform:translateY(0);

}

}

.login-card{

width:470px;

padding:55px 50px;

background:linear-gradient(180deg,#311d57,#261845);

border-radius:32px;

box-shadow:0 30px 70px rgba(0,0,0,.40);

display:flex;

flex-direction:column;

align-items:center;

text-align:center;

border:1px solid rgba(255,255,255,.05);

}
form{

width:100%;

margin-top:20px;

}
h1{

font-size:64px;

margin-top:25px;

margin-bottom:10px;

font-weight:800;

letter-spacing:2px;

}


.logo{

width:140px;

margin-bottom:25px;

border-radius:20px;

box-shadow:0 12px 30px rgba(0,0,0,.30);

}

.inputBox{

display:flex;

align-items:center;

background:#50357d;

padding:16px;

border-radius:14px;

margin-bottom:18px;

}

.inputBox i{

color:#B7FF00;

font-size:18px;

margin-right:15px;

}

.inputBox input{
    background:none;
    border:none;
    width:100%;
    outline:none;
    font-size:15px;
    color:white;
    margin:0;
    padding:0;
}

.inputBox input::placeholder{
    color:#ddd;
    opacity:1;
    transition:opacity .2s ease;
}

.inputBox input:focus::placeholder{
    opacity:0;
}
button{

height:60px;

border-radius:15px;

font-size:18px;

font-weight:700;

background:#B7FF00;

transition:.35s;

}

button:hover{

transform:translateY(-4px);

box-shadow:0 10px 25px rgba(183,255,0,.35);

}

.footballBg{

position:fixed;

left:60px;

top:50%;

transform:translateY(-50%);

font-size:260px;

color:#B7FF00;

opacity:.04;

pointer-events:none;

animation:floatBall 6s ease-in-out infinite;

}

@keyframes floatBall{

0%{

transform:translateY(-50%);

}

50%{

transform:translateY(-55%);

}

100%{

transform:translateY(-50%);

}

}


</style>

</head>

<body>
<div class="footballBg">
    <i class="fa-solid fa-futbol"></i>
</div>
<div class="login-card">

<img src="assets/images/liger_logo.png" class="logo" alt="LIGER Logo">

<h1>LIGER</h1>

<p class="subtitle">

Football Academy Management System

</p>

<form method="POST">

<div class="inputBox">

    <i class="fa-solid fa-user"></i>

    <input
        type="text"
        name="username"
        placeholder="Username"
        required>

</div>

<div class="inputBox">

    <i class="fa-solid fa-lock"></i>

    <input
        type="password"
        name="password"
        placeholder="Password"
        required>

</div>

<button type="submit">

LOGIN

</button>

</form>

<?php

if($error!=""){

echo "<div class='error'>$error</div>";

}

?>

<div class="footer">

© 2026 LIGER Football Academy

</div>

</div>
<script>

document.querySelectorAll('.inputBox').forEach(function(box) {

    box.addEventListener('click', function() {

        const input = this.querySelector('input');

        if (input) {
            input.focus();
        }

    });

});

</script>
</body>

</html>