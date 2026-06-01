<?php
session_start();
include 'includes/db.php';

$message = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            header("Location: items.php");
            exit();
        } else {
            $message = "Wrong Password";
        }
    } else {
        $message = "User Not Found";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Login</title>

<link rel="stylesheet" href="css/style.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body class="auth-body">

<div class="auth-container">

<div class="auth-card">

<div class="auth-icon">

<i class="fa-solid fa-magnifying-glass-location"></i>

</div>

<h1>Welcome Back</h1>

<p>Login to report and claim lost items</p>

<?php if ($message != "") { ?>

<div class="error-message">

<?php echo htmlspecialchars($message); ?>

</div>

<?php } ?>

<form method="POST">

<div class="input-group">

<i class="fa-solid fa-envelope"></i>

<input
type="email"
name="email"
placeholder="Enter Email"
required>

</div>

<div class="input-group">

<i class="fa-solid fa-lock"></i>

<input
type="password"
name="password"
placeholder="Enter Password"
required>

</div>

<button name="login" class="auth-btn">

<i class="fa-solid fa-right-to-bracket"></i>

Login

</button>

</form>

<div class="auth-link">

Don't have an account?

<a href="register.php">

Register

</a>

<br><br>

<a href="admin/index.php">

Admin Login

</a>

</div>

</div>

</div>

</body>
</html>
