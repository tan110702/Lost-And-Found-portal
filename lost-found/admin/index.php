<?php
session_start();
include '../includes/db.php';

$message = "";

$admin_email = "admin@lostfound.com";
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);

$check_admin = $conn->prepare("SELECT id FROM users WHERE email=?");
$check_admin->bind_param("s", $admin_email);
$check_admin->execute();
$admin_result = $check_admin->get_result();

if ($admin_result->num_rows == 0) {
    $admin_name = "Admin";
    $admin_role = "admin";

    $create_admin = $conn->prepare(
        "INSERT INTO users(name,email,password,role)
        VALUES(?,?,?,?)"
    );

    $create_admin->bind_param(
        "ssss",
        $admin_name,
        $admin_email,
        $admin_password,
        $admin_role
    );

    $create_admin->execute();
} else {
    $existing_admin = $admin_result->fetch_assoc();
    $admin_role = "admin";

    $update_admin = $conn->prepare("UPDATE users SET role=? WHERE id=?");
    $update_admin->bind_param("si", $admin_role, $existing_admin['id']);
    $update_admin->execute();
}

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND role='admin'");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];

            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Wrong Password";
        }
    } else {
        $message = "Admin Not Found";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Admin Login</title>

<link rel="stylesheet" href="../css/style.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body class="auth-body">

<div class="auth-container">

<div class="auth-card">

<div class="auth-icon">
<i class="fa-solid fa-user-shield"></i>
</div>

<h1>Admin Login</h1>

<p>Manage lost and found items</p>

<?php if ($message != "") { ?>

<div class="error-message">
<?php echo htmlspecialchars($message); ?>
</div>

<?php } ?>

<form method="POST">

<div class="input-group">
<i class="fa-solid fa-envelope"></i>
<input type="email" name="email" placeholder="Enter Email" required>
</div>

<div class="input-group">
<i class="fa-solid fa-lock"></i>
<input type="password" name="password" placeholder="Enter Password" required>
</div>

<button name="login" class="auth-btn">
<i class="fa-solid fa-right-to-bracket"></i>
Login
</button>

</form>

<div class="auth-link">

User side?

<a href="../login.php">

Login Here

</a>

</div>

</div>

</div>

</body>
</html>
