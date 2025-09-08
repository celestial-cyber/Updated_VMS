<?php
session_start();
include 'connection.php';

if (isset($_REQUEST['login_btn'])) {
    $email = $_POST['email'];
    $pwd   = md5($_POST['pwd']);
    $role  = $_POST['role']; // ✅ from hidden input or dropdown

    if ($role === "admin") {
        $select_query = mysqli_query($conn, "SELECT id, user_name FROM tbl_admin WHERE emailid='$email' AND password='$pwd'");
    } else {
        $select_query = mysqli_query($conn, "SELECT id, member_name FROM tbl_members WHERE emailid='$email' AND password='$pwd'");
    }

    $rows = mysqli_num_rows($select_query);
    if ($rows > 0) {
        $username = mysqli_fetch_row($select_query);

        // ✅ Store session values
        $_SESSION['id']   = $username[0];
        $_SESSION['name'] = $username[1];
        $_SESSION['role'] = $role; // ✅ used by landing.php

        // ✅ Always redirect to landing page first
        header("Location: landing.php");
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

  <!-- Custom fonts for this template-->
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <!-- Custom styles for this template-->
  <link href="css/sb-admin.css" rel="stylesheet">
  <link href="css/custom_style.css?ver=1.1" rel="stylesheet">

  <style>
  /* ✅ Banner card wrapper */
  .banner {
      text-align: center;
      margin-top: 0px;
      margin-bottom: 10px;
      max-width: 1000px;
      margin-left: auto;
      margin-right: auto;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      overflow: hidden;
  }
  .banner img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      display: block;
  }

  /* ✅ Role selection section */
  .role-buttons {
      margin-top: 15px;
      text-align: center;
  }
  .role-buttons h3 {
      font-size: 22px;
      margin-bottom: 5px;
  }
  .role-buttons p {
      font-weight: bold;
      color: #004d00; /* ✅ dark green */
      font-size: 18px;
      margin-bottom: 20px;
  }

  /* ✅ Buttons with green theme */
  .btn-role {
      display: inline-block;
      width: 220px;
      height: 60px;
      margin: 10px;
      border-radius: 0;      
      border: 2px solid #004d00;
      background-color: #004d00;
      color: white;
      font-size: 18px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
  }
  .btn-role:hover {
      background-color: white;
      color: #004d00;
  }

  /* ✅ Login card */
  .card-login {
      margin: 0 auto;
      margin-top: 20px;
      max-width: 400px;
      padding: 20px;
      display: none; /* hidden by default */
  }

  /* ✅ Input fields matching green shade */
  .form-control {
      border: 1px solid #28a745;
  }
  .form-control:focus {
      border-color: #004d00;
      box-shadow: 0 0 0 0.2rem rgba(0,77,0,0.25);
  }

  body {
      background-color: #e6f4ea;
  }
  </style>

  <script>
    function showLogin(role) {
        document.getElementById('role-selection').style.display = 'none';
        document.getElementById('login-card').style.display = 'block';
        document.getElementById('role').value = role; // set hidden role input
        document.getElementById('login-title').innerText = "Login as " + role.charAt(0).toUpperCase() + role.slice(1);
    }
    function goBack() {
        document.getElementById('login-card').style.display = 'none';
        document.getElementById('role-selection').style.display = 'block';
    }
  </script>
</head>
<body>

  <!-- ✅ Banner at the top -->
  <div class="banner">
    <img src="Images/SABanner.png" alt="Specanciens Banner">
  </div>

  <!-- ✅ Role Selection -->
  <div id="role-selection" class="role-buttons">
    <h3>Login to Visitor Management System</h3>
    <p>Please choose your login type:</p>

    <!-- ✅ Green theme buttons -->
    <button class="btn-role" onclick="showLogin('admin')">Admin Login</button>
    <button class="btn-role" onclick="showLogin('member')">Member Login</button>
  </div>

  <!-- ✅ Login Form (hidden until role chosen) -->
  <div id="login-card" class="card card-login mx-auto">
    <div class="card-header text-center">
      <h4 id="login-title">Login</h4>
    </div>
    <div class="card-body">
      <form name="login" method="post" action="">
        <input type="hidden" name="role" id="role" value="">
        <div class="form-group">
            <input type="email" id="inputEmail" class="form-control" name="email" placeholder="Email address" required autofocus>
        </div>
        <div class="form-group">
            <input type="password" id="inputPassword" class="form-control" placeholder="Password" name="pwd" required>
        </div>
        <input type="submit" class="btn btn-primary btn-block" name="login_btn" value="Login"
               style="background-color:#004d00; border-color:#004d00; color:white;">

        <!-- ✅ Back button styled -->
        <div class="form-group text-center">
          <button type="button" class="btn btn-success btn-sm" onclick="goBack()" 
                  style="background-color:#004d00; border-color:#004d00; color:white; margin-top:10px; padding:6px 20px;">
            ← Back
          </button>
        </div>
      </form>
    </div>
  </div>
    <!-- ✅ Sticky Footer -->
  <footer style="position:fixed; bottom:0; left:0; width:100%; 
                 background-color:#004d00; color:white; 
                 text-align:center; font-size:14px; 
                 padding:4px 0; z-index:1000;">
    © 2025 SPECANCIENS - All Rights Reserved.
  </footer>


</body>
</html>
