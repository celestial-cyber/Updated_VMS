<?php
session_start();
include 'connection.php';

// ✅ If not logged in → redirect to login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Detect role from session
$user_role = $_SESSION['role'] ?? 'member';

// ✅ Dashboard link based on role
$dashboard_link = ($user_role === 'admin') ? "admin_dashboard.php" : "member_dashboard.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Visitor Management System</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
body {
  margin: 0;
  font-family: Arial, sans-serif;
  /* Darker green gradient background */
  background: linear-gradient(to bottom, #b3d9b3, #ffffff);
}

/* Banner box */
.banner {
  position: relative;
  width: 100%;
  height: 200px;
  margin-bottom: 10px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  overflow: hidden;
}

.banner img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* Banner content layout */
.banner-content {
  position: absolute;
  top: 20px;
  left: 30px;
  right: 30px;
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

/* Logout button */
.logout-btn {
  background-color: #dc3545; /* Red theme */
  color: #fff;
  padding: 8px 18px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: bold;
  font-size: 14px;
  transition: background 0.3s;
  position: relative;
  top: 30px;
}

.logout-container {
  display: flex;
  justify-content: flex-end; /* moves button to right */
  align-items: center;       /* keeps vertical alignment */
  padding: 20px;             /* adjust spacing as needed */
}

.logout-btn {
  background-color: #dc3545;
  color: #fff;
  padding: 8px 18px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: bold;
  font-size: 14px;
  transition: background 0.3s;
}

.logout-btn:hover {
  background-color: #c82333;
  color: #fff;
}

/* Tablet */
@media (max-width: 768px) {
  .logout-btn {
    font-size: 12px;
    padding: 12px 14px;
  }
}

/* Mobile */
@media (max-width: 480px) {
  .logout-btn {
    font-size: 11px;
    padding: 6px 12px;
  }
}

/* Center content card with green gradient */
.center-box {
  border: 1px solid #ccc;
  border-radius: 12px;
  margin: 40px auto;
  max-width: 900px;
  padding: 120px 40px 40px 40px; /* extra top padding for logos */
  text-align: center;
  position: relative; /* For logo positioning */
  background: linear-gradient(to bottom, #d9f0d9, #ffffff);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

/* Logos inside the card - bigger and diagonally lower */
.logo-left {
  max-height: 150px;
  position: absolute;
  top: 60px;   /* move diagonally down */
  left: 40px;
}

.logo-right {
  max-height: 120px;
  position: absolute;
  top: 60px;   /* move diagonally down */
  right: 30px;
}

/* Illustration */
/* Illustration */
/* Illustration */
.illustration {
  max-height: 200px;
  margin: -80px auto -25px auto; /* top 10px, bottom 20px, centered */
  display: block;
}


/* Get Started Button */
.btn-theme {
  background-color: #044210ff;
  color: #fff;
  font-size: 16px;
  padding: 10px 20px;
  border-radius: 6px;
  text-decoration: none;
  border: none;
}
.btn-theme:hover {
  background-color: #0da114ff;
  color: #fff;
}
/* Visitor Management System title */
/* Visitor Management System title */
/* Visitor Management System title */
/* Visitor Management System title */
/* Visitor Management System title */
/* Visitor Management System title */
.main-title {
  font-family: 'Times New Roman', Times, serif;
  font-size: 45px;           /* slightly larger */
  font-weight: bold;
  color: #0d3d12;            /* even darker green */
  text-align: left;
  margin-left: 150px;        /* align with left logo */
  margin-top: -25px;         /* move slightly upward */
  margin-bottom: 20px;
}
/* L&D Initiative subtitle */
/* L&D Initiative subtitle */
/* L&D Initiative subtitle on the right */
/* L&D Initiative subtitle on the right */
.subtitle {
  font-family: Arial, sans-serif;
  font-size: 16px;          /* smaller font */
  font-weight: bold;
  color: #000000;           /* black */
  text-align: right;         /* align text to right */
  margin-right: 130px;        /* distance from right edge */
  margin-top: -15px;          /* closer to main title */
  margin-bottom: 20px;
}




</style>
</head>
<body>

<!-- Banner -->
<div class="banner">
  <img src="Images\banner.png" alt="VMS Banner">
  <div class="banner-content">
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
</div>

<!-- Main Center Card -->
<div class="center-box">
  <!-- Logos inside card -->
  <img src="Images/SALogo.png" class="logo-left" alt="SA Logo">
  

  <!-- SPECANCIENS PRESENTS next to left logo -->
  <div style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 30px; margin-left: 150px; margin-top: -20px;">
    <h5 style="font-weight: bold; font-size: 18px; margin: 0;">SPECANCIENS PRESENTS</h5>
</div>


  <!-- Card Content -->
<h2 class="main-title">
  Visitor <span style="color:#145a20;">Management</span> System
</h2>



<h5 class="subtitle">An L&D Initiative</h5>


  <img src="Images\buildings.png" alt="Illustration" class="illustration">

  <!-- Get Started Button -->
  <div class="mt-3">
    <a href="<?php echo $dashboard_link; ?>" class="btn btn-theme">Click to Get Started</a>
  </div>
</div>

</body>
</html>
