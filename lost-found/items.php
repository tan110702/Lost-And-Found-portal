<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = (int)$_SESSION['user_id'];
$search = trim($_GET['search'] ?? "");
$type = $_GET['type'] ?? "all";
$status = $_GET['status'] ?? "all";
$claim_message = "";

if (isset($_POST['claim_item'])) {
    $item_id = (int)$_POST['item_id'];
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);

    if ($message == "") {
        $message = "Phone verification submitted.";
    }

    if (!preg_match("/^[0-9+\-\s()]{7,20}$/", $phone)) {
        $claim_message = "Enter a valid phone number";
    } else {
        $owner_stmt = $conn->prepare("SELECT user_id FROM items WHERE id=?");
        $owner_stmt->bind_param("i", $item_id);
        $owner_stmt->execute();
        $item_data = $owner_stmt->get_result()->fetch_assoc();

        if ($item_data && (int)$item_data['user_id'] != $user_id) {
            $stmt = $conn->prepare(
                "INSERT INTO claims(item_id,user_id,phone,message)
                VALUES(?,?,?,?)"
            );
            $stmt->bind_param("iiss", $item_id, $user_id, $phone, $message);

            if ($stmt->execute()) {
                $claim_message = "Claim Sent Successfully";
            } else {
                $claim_message = "You already claimed this item";
            }
        } else {
            $claim_message = "You cannot claim your own item";
        }
    }
}

$where = [];
$params = [];
$types = "";

if ($search != "") {
    $where[] = "(items.title LIKE ? OR items.description LIKE ? OR items.location LIKE ?)";
    $keyword = "%" . $search . "%";
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $types .= "sss";
}

if ($type == "lost" || $type == "found") {
    $where[] = "items.type=?";
    $params[] = $type;
    $types .= "s";
}

$status_sql = "COALESCE((
    SELECT status
    FROM claims
    WHERE claims.item_id = items.id
    ORDER BY FIELD(status, 'approved', 'pending', 'rejected'), created_at DESC
    LIMIT 1
), 'none')";

if (in_array($status, ['none', 'pending', 'approved', 'rejected'], true)) {
    $where[] = $status_sql . "=?";
    $params[] = $status;
    $types .= "s";
}

$where_sql = "";

if (count($where) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where);
}

$sql = "SELECT items.*, users.name AS poster_name, " . $status_sql . " AS claim_status
        FROM items
        JOIN users ON items.user_id = users.id
        " . $where_sql . "
        ORDER BY items.created_at DESC";

$stmt = $conn->prepare($sql);

if ($types != "") {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$items = $stmt->get_result();

$total_items = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM items"))['total'];
$lost_items = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM items WHERE type='lost'"))['total'];
$found_items = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM items WHERE type='found'"))['total'];
?>

<!DOCTYPE html>
<html>
<head>

<title>Items</title>

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

<a href="items.php">
<i class="fa-solid fa-house"></i>
Items
</a>

<a href="post_item.php">
<i class="fa-solid fa-circle-plus"></i>
Post Item
</a>

<a href="claims.php">
<i class="fa-solid fa-clipboard-check"></i>
Claims
</a>

<a href="logout.php">
<i class="fa-solid fa-right-from-bracket"></i>
Logout
</a>

</div>

</div>

<div class="menu-header hero-header">

<h1>Lost & Found Board</h1>

<p>Search items, post missing things, and claim your belongings easily</p>

<a class="hero-btn" href="post_item.php">
<i class="fa-solid fa-circle-plus"></i>
Post New Item
</a>

</div>

<?php if ($claim_message != "") { ?>

<div class="success-message">

<?php echo htmlspecialchars($claim_message); ?>

</div>

<?php } ?>

<div class="dashboard-cards small-dashboard">

<div class="dashboard-card">
<i class="fa-solid fa-box"></i>
<h2><?php echo $total_items; ?></h2>
<p>Total Items</p>
</div>

<div class="dashboard-card">
<i class="fa-solid fa-circle-question"></i>
<h2><?php echo $lost_items; ?></h2>
<p>Lost Items</p>
</div>

<div class="dashboard-card">
<i class="fa-solid fa-hand-holding-heart"></i>
<h2><?php echo $found_items; ?></h2>
<p>Found Items</p>
</div>

</div>

<div class="filter-box">

<form method="GET">

<input
type="text"
name="search"
placeholder="Search title, location, or details"
value="<?php echo htmlspecialchars($search); ?>">

<select name="type">
<option value="all">All Types</option>
<option value="lost" <?php if ($type == "lost") echo "selected"; ?>>Lost</option>
<option value="found" <?php if ($type == "found") echo "selected"; ?>>Found</option>
</select>

<select name="status">
<option value="all">All Claim Status</option>
<option value="none" <?php if ($status == "none") echo "selected"; ?>>No Claims</option>
<option value="pending" <?php if ($status == "pending") echo "selected"; ?>>Pending</option>
<option value="approved" <?php if ($status == "approved") echo "selected"; ?>>Approved</option>
<option value="rejected" <?php if ($status == "rejected") echo "selected"; ?>>Rejected</option>
</select>

<button>
<i class="fa-solid fa-filter"></i>
Filter
</button>

</form>

</div>

<div class="food-container">

<?php if ($items->num_rows == 0) { ?>

<div class="empty-orders">
<i class="fa-solid fa-box-open"></i>
<h2>No items found</h2>
</div>

<?php } ?>

<?php while ($item = $items->fetch_assoc()) { ?>

<div class="food-card item-card">

<div class="image-box">

<img
src="uploads/<?php echo htmlspecialchars($item['image']); ?>"
class="food-image"
alt="Item Image">

</div>

<div class="food-content">

<div class="badge-row">
<span class="status-badge <?php echo htmlspecialchars($item['type']); ?>">
<?php echo htmlspecialchars($item['type']); ?>
</span>
<span class="status-badge <?php echo htmlspecialchars($item['claim_status']); ?>">
<?php echo $item['claim_status'] == "none" ? "No Claims" : htmlspecialchars($item['claim_status']); ?>
</span>
</div>

<h2><?php echo htmlspecialchars($item['title']); ?></h2>

<p><?php echo htmlspecialchars($item['description']); ?></p>

<p class="item-meta">
<i class="fa-solid fa-location-dot"></i>
<?php echo htmlspecialchars($item['location']); ?>
</p>

<p class="item-meta">
Posted by <?php echo htmlspecialchars($item['poster_name']); ?>
</p>

<?php if ((int)$item['user_id'] == $user_id) { ?>

<p class="owner-note">Posted by you</p>

<?php } elseif ($item['claim_status'] != "approved") { ?>

<form method="POST" class="claim-form">

<input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">

<input
type="tel"
name="phone"
placeholder="Phone number"
required>

<textarea
name="message"
placeholder="Verification note"></textarea>

<button name="claim_item">
<i class="fa-solid fa-paper-plane"></i>
Claim Item
</button>

</form>

<?php } ?>

</div>

</div>

<?php } ?>

</div>

</body>
</html>
