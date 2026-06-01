<?php
include 'includes/db.php';

$message = "";
$message_class = "error-message";

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (strlen($password) < 6) {
        $message = "Password must be 6 characters";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO users(name,email,password)
            VALUES(?,?,?)"
        );

        $stmt->bind_param(
            "sss",
            $name,
            $email,
            $hashed_password
        );

        if ($stmt->execute()) {
            $message = "Registration Successful";
            $message_class = "success-message";
        } else {
            $message = "Email already exists";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Register</title>

<link rel="stylesheet" href="css/style.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body class="auth-body">

<div class="auth-container">

<div class="auth-card">

<div class="auth-icon">

<i class="fa-solid fa-user-plus"></i>

</div>

<h1>Create Account</h1>

<p>Register to use the Lost & Found system</p>

<?php if ($message != "") { ?>

<div class="<?php echo $message_class; ?>">

<?php echo htmlspecialchars($message); ?>

</div>

<?php } ?>

<form method="POST">

<div class="input-group">

<i class="fa-solid fa-user"></i>

<input
type="text"
name="name"
placeholder="Enter Full Name"
required>

</div>

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

<button name="register" class="auth-btn">

<i class="fa-solid fa-user-plus"></i>

Register

</button>

</form>

<div class="auth-link">

Already have an account?

<a href="login.php">

Login

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
