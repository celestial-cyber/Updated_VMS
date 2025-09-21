<?php
session_start();
include 'connection.php'; // Make sure this file does NOT echo anything

// Handle login form submission
if (isset($_POST['login_btn'])) {
    $email = $_POST['email'];
    $pwd   = md5($_POST['pwd']);
    $role  = $_POST['role'];

    if ($role === "admin") {
        $select_query = mysqli_query($conn, "SELECT id, user_name FROM tbl_admin WHERE emailid='$email' AND password='$pwd'");
    } else {
        $select_query = mysqli_query($conn, "SELECT id, member_name FROM tbl_members WHERE emailid='$email' AND password='$pwd'");
    }

    if (mysqli_num_rows($select_query) > 0) {
        $username = mysqli_fetch_row($select_query);
        $_SESSION['id']   = $username[0];
        $_SESSION['name'] = $username[1];
        $_SESSION['role'] = $role;

        // Redirect to dashboard
        $dashboard_link = ($role === 'admin') ? "admin_dashboard.php" : "member_dashboard.php";
        header("Location: $dashboard_link");
        exit();
    } else {
        echo "<script>alert('You have entered wrong email id or password.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Visitor Management System</title>

<link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
<link href="css/sb-admin.css" rel="stylesheet">
<link href="css/custom_style.css?ver=1.2" rel="stylesheet">

<style>
body {
    background-color: #e6f4ea;
    font-family: Arial, sans-serif;
}

/* Banner */
.banner {
    text-align: center;
    margin: 0 auto 15px auto;
    max-width: 1000px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    overflow: hidden;
}
.banner img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

/* Role selection */
.role-buttons {
    text-align: center;
    margin-top: 10px;
}
.role-buttons h3 {
    font-size: 24px;
    margin-bottom: 5px;
    color: #004d00;
}
.role-buttons p {
    font-weight: bold;
    color: #004d00;
    font-size: 18px;
    margin-bottom: 20px;
}

/* Green theme buttons */
.btn-role {
    display: inline-block;
    width: 220px;
    height: 60px;
    margin: 10px;
    border-radius: 5px;
    border: 2px solid #004d00;
    background-color: #004d00;
    color: white;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}
.btn-role:hover {
    background-color: white;
    color: #004d00;
}

/* Login card */
.card-login {
    margin: 20px auto;
    max-width: 400px;
    padding: 20px;
    display: none;
    border-radius: 10px;
    border: 1px solid #28a745;
    box-shadow: 0 4px 10px rgba(0,77,0,0.2);
    background-color: white;
}

/* Input fields */
.form-control {
    border: 1px solid #28a745;
    border-radius: 5px;
}
.form-control:focus {
    border-color: #004d00;
    box-shadow: 0 0 0 0.2rem rgba(0,77,0,0.25);
}

/* Submit button */
.btn-login {
    background-color: #004d00;
    border-color: #004d00;
    color: white;
    font-weight: bold;
    transition: all 0.3s ease;
}
.btn-login:hover {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

/* Back button */
.btn-back {
    background-color: #004d00;
    border-color: #004d00;
    color: white;
    padding: 6px 20px;
    font-size: 14px;
    margin-top:15px;
    display: inline-block;
}
.btn-back:hover {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

/* Footer */
footer {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background-color: #004d00;
    color: white;
    text-align: center;
    font-size: 14px;
    padding: 4px 0;
    z-index: 1000;
}
</style>

<script>
function showLogin(role) {
    document.getElementById('role-selection').style.display = 'none';
    document.getElementById('login-card').style.display = 'block';
    document.getElementById('role').value = role;
    document.getElementById('login-title').innerText = "Login as " + role.charAt(0).toUpperCase() + role.slice(1);
}
function goBack() {
    document.getElementById('login-card').style.display = 'none';
    document.getElementById('role-selection').style.display = 'block';
}
</script>
</head>
<body>

<!-- Banner -->
<div class="banner">
    <img src="Images/SABanner.png" alt="Specanciens Banner">
</div>

<!-- Role Selection -->
<div id="role-selection" class="role-buttons">
    <h3>Login to Visitor Management System</h3>
    <p>Please choose your login type:</p>
    <button class="btn-role" onclick="showLogin('admin')">Admin Login</button>
    <button class="btn-role" onclick="showLogin('member')">Member Login</button>
</div>

<!-- Login Form -->
<div id="login-card" class="card card-login">
    <div class="card-header text-center">
        <h4 id="login-title">Login</h4>
    </div>
    <div class="card-body">
        <form method="post" action="">
            <input type="hidden" name="role" id="role" value="">
            <div class="form-group">
                <input type="email" id="inputEmail" class="form-control" name="email" placeholder="Email address" required autofocus>
            </div>
            <div class="form-group">
                <input type="password" id="inputPassword" class="form-control" name="pwd" placeholder="Password" required>
            </div>
            <input type="submit" class="btn btn-login btn-block" name="login_btn" value="Login">
            <div class="form-group text-center">
                <button type="button" class="btn btn-back" onclick="goBack()">← Back</button>
            </div>
        </form>
    </div>
</div>

<footer>
    © 2025 SPECANCIENS - All Rights Reserved.
</footer>

</body>
</html>
