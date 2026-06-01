<?php
include 'admin_auth.php';
include '../includes/db.php';

$items = mysqli_query(
    $conn,
    "SELECT items.*, users.name AS user_name, users.email AS user_email
    FROM items
    JOIN users ON items.user_id = users.id
    ORDER BY items.created_at DESC"
);
?>

<!DOCTYPE html>
<html>
<head>

<title>Admin Items</title>

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

<div class="table-page">

<div class="page-heading">
<h1>
<i class="fa-solid fa-box"></i>
All Items
</h1>
<p>Review all items submitted by users.</p>
</div>

<?php if (mysqli_num_rows($items) == 0) { ?>

<div class="empty-orders">
<i class="fa-solid fa-box-open"></i>
<h2>No items posted yet</h2>
</div>

<?php } else { ?>

<div class="orders-wrapper">

<table class="modern-orders-table">

<tr>
<th>Image</th>
<th>Title</th>
<th>Type</th>
<th>Location</th>
<th>Posted By</th>
<th>Date</th>
</tr>

<?php while ($item = mysqli_fetch_assoc($items)) { ?>

<tr>

<td>
<img
src="../uploads/<?php echo htmlspecialchars($item['image']); ?>"
class="table-image"
alt="Item Image">
</td>

<td><?php echo htmlspecialchars($item['title']); ?></td>

<td>
<span class="status-badge <?php echo htmlspecialchars($item['type']); ?>">
<?php echo htmlspecialchars($item['type']); ?>
</span>
</td>

<td><?php echo htmlspecialchars($item['location']); ?></td>

<td>
<?php echo htmlspecialchars($item['user_name']); ?><br>
<small><?php echo htmlspecialchars($item['user_email']); ?></small>
</td>

<td><?php echo htmlspecialchars($item['created_at']); ?></td>

</tr>

<?php } ?>

</table>

</div>

<?php } ?>

</div>

</body>
</html>
