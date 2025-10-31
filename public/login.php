<?php
// Start session
session_start();

// If user already logged in, redirect to index
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("Location: index.php");
    exit;
}

// ✅ Include database configuration
require_once __DIR__ . '/db_config.php';

// Initialize variables
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Process login form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter your username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // If no errors, try login
    if (empty($username_err) && empty($password_err)) {

        $sql = "SELECT id, username, password_hash FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // ✅ Successful login
                            session_regenerate_id(true); // Prevent session fixation
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;

                            header("Location: index.php");
                            exit;
                        } else {
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    $login_err = "Invalid username or password.";
                }
            } else {
                $login_err = "Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GadgetHut</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">

<div class="w-full max-w-md bg-white p-8 rounded-xl shadow-lg">
    <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Login</h2>

    <?php if (!empty($login_err)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($login_err) ?>
        </div>
    <?php endif; ?>

    <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-6">
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input type="text" name="username" id="username" value="<?= htmlspecialchars($username) ?>"
                   class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 <?= !empty($username_err) ? 'border-red-500' : 'border-gray-300' ?>"
                   required>
            <?php if (!empty($username_err)): ?>
                <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($username_err) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" id="password"
                   class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 <?= !empty($password_err) ? 'border-red-500' : 'border-gray-300' ?>"
                   required>
            <?php if (!empty($password_err)): ?>
                <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($password_err) ?></p>
            <?php endif; ?>
        </div>

        <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg shadow-md transition duration-200">
            Login
        </button>
    </form>

    <p class="text-center text-sm text-gray-600 mt-6">
        Don't have an account?
        <a href="register.php" class="text-blue-600 hover:text-blue-800 font-semibold">Sign up here</a>.
    </p>
</div>

<script>
// Prevent going back to secured pages after logout
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// Clear forward history
window.addEventListener('popstate', function(event) {
    window.history.forward();
});
</script>

</body>
</html>