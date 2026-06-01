<?php
include 'admin_auth.php';
include '../includes/db.php';

$message = "";

if (isset($_GET['action']) && isset($_GET['id'])) {
    $claim_id = (int)$_GET['id'];
    $status = $_GET['action'];

    if (in_array($status, ['approved', 'rejected'], true)) {
        $stmt = $conn->prepare("UPDATE claims SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $claim_id);

        if ($stmt->execute()) {
            $message = "Claim Updated";
        }
    }
}

$claims = mysqli_query(
    $conn,
    "SELECT claims.*, items.title, items.image,
    owner.name AS owner_name,
    claimant.name AS claimant_name,
    claimant.email AS claimant_email
    FROM claims
    JOIN items ON claims.item_id = items.id
    JOIN users owner ON items.user_id = owner.id
    JOIN users claimant ON claims.user_id = claimant.id
    ORDER BY claims.created_at DESC"
);
?>

<!DOCTYPE html>
<html>
<head>

<title>Admin Claims</title>

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
<i class="fa-solid fa-clipboard-check"></i>
All Claims
</h1>
<p>Track every claim request in one place.</p>
</div>

<?php if ($message != "") { ?>

<div class="success-message">
<?php echo htmlspecialchars($message); ?>
</div>

<?php } ?>

<?php if (mysqli_num_rows($claims) == 0) { ?>

<div class="empty-orders">
<i class="fa-solid fa-inbox"></i>
<h2>No claims yet</h2>
</div>

<?php } else { ?>

<div class="orders-wrapper">

<table class="modern-orders-table">

<tr>
<th>Image</th>
<th>Item</th>
<th>Owner</th>
<th>Claimant</th>
<th>Phone</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while ($claim = mysqli_fetch_assoc($claims)) { ?>

<tr>

<td>
<img
src="../uploads/<?php echo htmlspecialchars($claim['image']); ?>"
class="table-image"
alt="Item Image">
</td>

<td><?php echo htmlspecialchars($claim['title']); ?></td>

<td><?php echo htmlspecialchars($claim['owner_name']); ?></td>

<td>
<?php echo htmlspecialchars($claim['claimant_name']); ?><br>
<small><?php echo htmlspecialchars($claim['claimant_email']); ?></small>
</td>

<td><?php echo htmlspecialchars($claim['phone']); ?></td>

<td>
<span class="status-badge <?php echo htmlspecialchars($claim['status']); ?>">
<?php echo htmlspecialchars($claim['status']); ?>
</span>
</td>

<td>

<?php if ($claim['status'] == "pending") { ?>

<div class="order-action-buttons">

<a class="ready-btn" href="claims.php?action=approved&id=<?php echo $claim['id']; ?>">
Approve
</a>

<a class="prepare-btn" href="claims.php?action=rejected&id=<?php echo $claim['id']; ?>">
Reject
</a>

</div>

<?php } else { ?>

Done

<?php } ?>

</td>

</tr>

<?php } ?>

</table>

</div>

<?php } ?>

</div>

</body>
</html>
