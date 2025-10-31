<?php
// ✅ Start a fresh session (destroy old one to avoid redirect loop)
session_start();
session_unset();
session_destroy();
session_start();

// ✅ Include database config
require_once __DIR__ . '/../includes/db_config.php';

$message = "";

// ✅ Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    $password = isset($_POST["password"]) ? trim($_POST["password"]) : "";

    // Validate inputs
    if ($username === "" || $email === "" || $password === "") {
        $message = "⚠️ All fields are required.";
    } else {
        // ✅ Check if username already exists
        $check_sql = "SELECT id FROM users WHERE username = ?";
        if ($check_stmt = mysqli_prepare($conn, $check_sql)) {
            mysqli_stmt_bind_param($check_stmt, "s", $username);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);

            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $message = "❌ Username already taken. Please choose another.";
            } else {
                // ✅ Hash password before storing
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // ✅ Insert new user into the database
                $sql = "INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())";
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashed_password);
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "✅ Registration successful! <a href='login.php'>Login here</a>";
                    } else {
                        $message = "❌ Database error: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $message = "❌ Failed to prepare statement: " . mysqli_error($conn);
                }
            }
            mysqli_stmt_close($check_stmt);
        }
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration - MCommerce</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card mx-auto shadow-lg" style="max-width: 500px;">
        <div class="card-body">
            <h3 class="card-title text-center mb-4 text-primary fw-bold">Create an Account</h3>

            <?php if (!empty($message)): ?>
                <div class="alert alert-info text-center"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold">Register</button>
            </form>

            <p class="text-center mt-3 mb-0">
                Already have an account? 
                <a href="login.php" class="text-decoration-none fw-semibold">Login here</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>
