
<?php
// Start the session at the very beginning
session_start();

// Include config file
require_once "db_config.php";

$username = $email = $password = $error = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate inputs
    if(empty(trim($_POST["username"])) || empty(trim($_POST["email"])) || empty(trim($_POST["password"]))){
        $error = "Please fill in all fields.";
    } else {
        $username = trim($_POST["username"]);
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);

        // Check if username or email already exists using prepared statements
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("ss", $param_username, $param_email);
            $param_username = $username;
            $param_email = $email;
            
            if($stmt->execute()){
                $stmt->store_result();
                
                if($stmt->num_rows > 0){
                    $error = "This username or email is already taken.";
                } else {
                    // Hash the password securely
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user into the database
                    $sql_insert = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
                    
                    if($stmt_insert = $conn->prepare($sql_insert)){
                        $stmt_insert->bind_param("sss", $param_username, $param_email, $param_password_hash);
                        $param_password_hash = $password_hash;
                        
                        if($stmt_insert->execute()){
                            // Redirect to login page
                            header("location: login.php?registered=success");
                            exit();
                        } else {
                            $error = "Oops! Something went wrong. Please try again later.";
                        }
                        $stmt_insert->close();
                    }
                }
            } else {
                $error = "Database execution error.";
            }
            $stmt->close();
        }
    }
    
    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-Commerce Register</title>
    <!-- Tailwind CSS CDN for responsive styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center font-sans">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-2xl border border-gray-100">
        <div class="text-center mb-6">
            <i class="fas fa-mobile-alt text-4xl text-indigo-600 mb-3"></i>
            <h2 class="text-3xl font-extrabold text-gray-900">
                Register Account
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Create your M-Commerce profile.
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 text-center" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <div class="mt-1">
                    <input id="username" name="username" type="text" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                <div class="mt-1">
                    <input id="email" name="email" type="email" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <div class="mt-1">
                    <input id="password" name="password" type="password" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    Create Account
                </button>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            Already have an account?
            <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-500">
                Sign in
            </a>
        </p>
    </div>
</body>
</html>
