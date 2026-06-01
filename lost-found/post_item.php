<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = (int)$_SESSION['user_id'];
$message = "";
$message_class = "error-message";

if (isset($_POST['post_item'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $type = $_POST['type'];
    $location = trim($_POST['location']);

    if ($title == "" || $description == "" || $location == "") {
        $message = "Please fill all fields";
    } elseif (!in_array($type, ['lost', 'found'], true)) {
        $message = "Select a valid item type";
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] != UPLOAD_ERR_OK) {
        $message = "Please upload an image";
    } else {
        $allowed_types = [
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/gif" => "gif",
            "image/webp" => "webp"
        ];

        $mime = mime_content_type($_FILES['image']['tmp_name']);

        if (!isset($allowed_types[$mime])) {
            $message = "Upload JPG, PNG, GIF, or WebP image";
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $message = "Image must be 5MB or smaller";
        } else {
            $upload_dir = __DIR__ . "/uploads/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $image = time() . "_" . bin2hex(random_bytes(6)) . "." . $allowed_types[$mime];
            $target = $upload_dir . $image;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $stmt = $conn->prepare(
                    "INSERT INTO items(title,description,type,image,location,user_id)
                    VALUES(?,?,?,?,?,?)"
                );

                $stmt->bind_param(
                    "sssssi",
                    $title,
                    $description,
                    $type,
                    $image,
                    $location,
                    $user_id
                );

                if ($stmt->execute()) {
                    $message = "Item Posted Successfully";
                    $message_class = "success-message";
                } else {
                    $message = "Item could not be saved";
                }
            } else {
                $message = "Image could not be uploaded";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Post Item</title>

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

<div class="form-page">

<div class="food-form-card">

<h1>
<i class="fa-solid fa-circle-plus"></i>
Post Item
</h1>

<?php if ($message != "") { ?>

<div class="<?php echo $message_class; ?>">

<?php echo htmlspecialchars($message); ?>

</div>

<?php } ?>

<form method="POST" enctype="multipart/form-data">

<label>Item Title</label>

<input
type="text"
name="title"
placeholder="Item title"
required>

<label>Description</label>

<textarea
name="description"
placeholder="Description"
required></textarea>

<label>Type</label>

<select name="type" required>
<option value="lost">Lost</option>
<option value="found">Found</option>
</select>

<label>Location</label>

<input
type="text"
name="location"
placeholder="Location"
required>

<label>Image</label>

<input
type="file"
name="image"
accept="image/*"
required>

<button name="post_item">
<i class="fa-solid fa-upload"></i>
Submit Item
</button>

</form>

</div>

</div>

</body>
</html>
