<?php
include 'admin_auth.php';
include '../includes/db.php';

$item_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM items"));
$lost_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM items WHERE type='lost'"));
$found_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM items WHERE type='found'"));
$claim_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM claims"));
$user_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='user'"));
?>

<!DOCTYPE html>
<html>
<head>

<title>Admin Dashboard</title>

<link rel="stylesheet" href="../css/style.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>

<div class="navbar">

<h2>
<i class="fa-solid fa-user-shield"></i>
Admin Panel
</h2>

<div>
<a href="dashboard.php">Dashboard</a>
<a href="items.php">Items</a>
<a href="claims.php">Claims</a>
<a href="logout.php">Logout</a>
</div>

</div>

<div class="admin-dashboard dashboard-page">

<div class="page-heading">
<h1>
<i class="fa-solid fa-chart-line"></i>
Dashboard Overview
</h1>
<p>Quick view of all lost and found activity.</p>
</div>

<div class="dashboard-cards">

<div class="dashboard-card">
<i class="fa-solid fa-box"></i>
<h2><?php echo $item_data['total']; ?></h2>
<p>Total Items</p>
</div>

<div class="dashboard-card">
<i class="fa-solid fa-circle-question"></i>
<h2><?php echo $lost_data['total']; ?></h2>
<p>Lost Items</p>
</div>

<div class="dashboard-card">
<i class="fa-solid fa-hand-holding-heart"></i>
<h2><?php echo $found_data['total']; ?></h2>
<p>Found Items</p>
</div>

<div class="dashboard-card">
<i class="fa-solid fa-clipboard-check"></i>
<h2><?php echo $claim_data['total']; ?></h2>
<p>Total Claims</p>
</div>

<div class="dashboard-card">
<i class="fa-solid fa-users"></i>
<h2><?php echo $user_data['total']; ?></h2>
<p>Total Users</p>
</div>

</div>

</div>

</body>
</html>
