<?php
// Start the session
session_start();

// Check if the user is logged in
$is_logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$username = $is_logged_in ? htmlspecialchars($_SESSION["username"]) : "Guest";

// Include database config
require_once "db_config.php";

$products = [];
$error_msg = "";

// Fetch products from the database
$sql = "SELECT id, name, description, price, image_url FROM products ORDER BY id DESC";

if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $result->free();
    }
} else {
    $error_msg = "Error fetching products: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Shop - Home</title>
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
                    colors: {
                        'primary': '#4f46e5',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans">

    <!-- Navigation Bar -->
    <nav class="bg-white shadow-md sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="index.php" class="flex-shrink-0 flex items-center">
                    <i class="fas fa-mobile-alt text-2xl text-primary mr-2"></i>
                    <span class="text-2xl font-bold text-gray-900">M-Shop</span>
                </a>
                
                <!-- User Status and Actions -->
                <div class="flex items-center space-x-4">
                    <?php if ($is_logged_in): ?>
                        <span class="text-gray-700 hidden sm:inline">Welcome, <span class="font-semibold text-primary"><?php echo $username; ?></span></span>
                        <!-- Updated Cart Link -->
                        <a href="checkout.php" class="text-gray-500 hover:text-primary transition duration-150 ease-in-out">
                            <i class="fas fa-shopping-cart text-xl"></i>
                        </a>
                        <a href="logout.php" class="px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-red-600 hover:bg-red-700 transition duration-150 ease-in-out">
                            Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 hover:text-primary font-medium">
                            Sign In
                        </a>
                        <a href="register.php" class="px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-primary hover:bg-indigo-700 transition duration-150 ease-in-out">
                            Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <header class="text-center mb-10">
            <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl">
                Mobile Commerce Store
            </h1>
            <p class="mt-3 text-xl text-gray-600">
                Your destination for the latest mobile technology.
            </p>
        </header>
        
        <!-- Success Message for Add to Cart -->
        <?php if (isset($_GET['status']) && $_GET['status'] == 'added'): ?>
            <div id="product-listing" class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-100 text-center" role="alert">
                <i class="fas fa-check-circle mr-2"></i> Item added to your cart successfully! <a href="checkout.php" class="font-semibold text-green-700 underline">View Cart</a>.
            </div>
        <?php endif; ?>


        <!-- Error Message -->
        <?php if (!empty($error_msg)): ?>
            <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-100 text-center" role="alert">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <!-- Product Grid -->
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 ease-in-out overflow-hidden">
                        <img class="w-full h-48 object-cover object-center" src="<?php echo $product['image_url']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.onerror=null;this.src='https://placehold.co/400x300/CCCCCC/000000?text=Image+Not+Found';">
                        
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($product['name']); ?></h2>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3"><?php echo htmlspecialchars($product['description']); ?></p>
                            
                            <div class="flex justify-between items-center mt-auto">
                                <span class="text-2xl font-bold text-primary">$<?php echo number_format($product['price'], 2); ?></span>
                                
                                <?php if ($is_logged_in): ?>
                                    <!-- Changed to a POST form for secure submission -->
                                    <form action="add_to_cart.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="px-4 py-2 bg-green-500 text-white text-sm font-medium rounded-lg shadow-md hover:bg-green-600 transition duration-150 ease-in-out">
                                            <i class="fas fa-cart-plus mr-1"></i> Add to Cart
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="login.php" class="px-4 py-2 bg-gray-400 text-white text-sm font-medium rounded-lg shadow-md hover:bg-gray-500 transition duration-150 ease-in-out">
                                        Login to Purchase
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="lg:col-span-3 text-center p-12 bg-white rounded-xl shadow-lg">
                    <p class="text-xl text-gray-500">No products are currently available.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 mt-10">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 text-center text-gray-400 text-sm">
            &copy; 2025 M-Shop. All rights reserved. | <span class="font-semibold">Developed with PHP, MySQL & AWS EC2.</span>
        </div>
    </footer>

</body>
</html>
