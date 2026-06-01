<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = (int)$_SESSION['user_id'];
$message = "";

if (isset($_GET['action']) && isset($_GET['id'])) {
    $claim_id = (int)$_GET['id'];
    $status = $_GET['action'];

    if (in_array($status, ['approved', 'rejected'], true)) {
        $stmt = $conn->prepare(
            "UPDATE claims
            JOIN items ON claims.item_id = items.id
            SET claims.status=?
            WHERE claims.id=? AND items.user_id=?"
        );

        $stmt->bind_param("sii", $status, $claim_id, $user_id);

        if ($stmt->execute()) {
            $message = "Claim Updated";
        }
    }
}

$stmt = $conn->prepare(
    "SELECT claims.id, claims.phone, claims.message, claims.status, claims.created_at,
    items.title, items.image,
    users.name AS claimant_name, users.email AS claimant_email
    FROM claims
    JOIN items ON claims.item_id = items.id
    JOIN users ON claims.user_id = users.id
    WHERE items.user_id=?
    ORDER BY claims.created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$claims = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>

<title>Claims</title>

<link rel="stylesheet" href="css/style.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>

<div class="navbar">

<h2>
<i class="fa-solid fa-magnifying-glass-location"></i>
Lost & Found
</h2>

<div>
<a href="items.php">Items</a>
<a href="post_item.php">Post Item</a>
<a href="claims.php">Claims</a>
<a href="logout.php">Logout</a>
</div>

</div>

<div class="table-page">

<div class="page-heading">
<h1>
<i class="fa-solid fa-clipboard-check"></i>
Claim Requests
</h1>
<p>Approve or reject requests for the items you posted.</p>
</div>

<?php if ($message != "") { ?>

<div class="success-message">
<?php echo htmlspecialchars($message); ?>
</div>

<?php } ?>

<?php if ($claims->num_rows == 0) { ?>

<div class="empty-orders">
<i class="fa-solid fa-inbox"></i>
<h2>No claims yet</h2>
<p>When someone claims your posted item, it will show here.</p>
</div>

<?php } else { ?>

<div class="orders-wrapper">

<table class="modern-orders-table">

<tr>
<th>Image</th>
<th>Item</th>
<th>Claimant</th>
<th>Phone</th>
<th>Note</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while ($claim = $claims->fetch_assoc()) { ?>

<tr>

<td>
<img
src="uploads/<?php echo htmlspecialchars($claim['image']); ?>"
class="table-image"
alt="Item Image">
</td>

<td><?php echo htmlspecialchars($claim['title']); ?></td>

<td>
<?php echo htmlspecialchars($claim['claimant_name']); ?><br>
<small><?php echo htmlspecialchars($claim['claimant_email']); ?></small>
</td>

<td><?php echo htmlspecialchars($claim['phone']); ?></td>

<td><?php echo htmlspecialchars($claim['message']); ?></td>

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
