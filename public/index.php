<?php
session_start();

// FIX: Corrected path using __DIR__ for reliable path resolution
require_once __DIR__ . '/../includes/db_config.php';

$is_logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$user_id = $is_logged_in ? $_SESSION["id"] : null;
$username = $is_logged_in ? htmlspecialchars($_SESSION["username"]) : "Guest";

// Handle success message from add_to_cart.php
$message = '';
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Fetch products
$products = [];
$sql = "SELECT id, name, price, description, image_url FROM products ORDER BY id DESC";
if ($result = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    mysqli_free_result($result);
} else {
    $message = "ERROR: Could not execute $sql. " . mysqli_error($conn);
}

// Function to safely display image URL (using placeholder for security/simplicity)
function get_product_image_url($url, $name) {
    // We use a placeholder since external images are not guaranteed to load, and we avoid file uploads.
    $w = 300; $h = 300;
    $color_code = substr(md5($name), 0, 6);
    $text = urlencode(substr($name, 0, 10));
    return "https://placehold.co/{$w}x{$h}/{$color_code}/ffffff?text={$text}";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Shop - Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .product-card { transition: transform 0.3s, box-shadow 0.3s; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="bg-gray-800 p-4 text-white shadow-lg sticky top-0 z-10">
    <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-2xl font-bold">M-Commerce Shop</h1>
        <div class="flex items-center space-x-4">
            <?php if ($is_logged_in): ?>
                <span class="text-sm hidden sm:inline">Welcome, <?= $username ?>!</span>
                <a href="checkout.php" class="text-yellow-400 hover:text-yellow-300 relative">
                    <!-- Cart Icon SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </a>
                <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-1 px-3 rounded-md transition duration-200">Logout</a>
            <?php else: ?>
                <a href="login.php" class="text-gray-300 hover:text-white transition duration-200">Login</a>
                <a href="register.php" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-1 px-3 rounded-md transition duration-200">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Success Message -->
<?php if ($message): ?>
<div id="messageBox" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 fixed top-16 left-1/2 transform -translate-x-1/2 w-full max-w-md z-20 rounded-md shadow-lg" role="alert">
    <p class="font-bold">Success!</p>
    <p><?= htmlspecialchars($message) ?></p>
</div>
<script>
    // Automatically fade out the success message after 4 seconds
    setTimeout(() => {
        const msgBox = document.getElementById('messageBox');
        if (msgBox) {
            msgBox.style.transition = 'opacity 1s';
            msgBox.style.opacity = '0';
            setTimeout(() => msgBox.style.display = 'none', 1000);
        }
    }, 4000);
</script>
<?php endif; ?>

<!-- Product Grid -->
<div class="container mx-auto p-6 mt-4">
    <h2 class="text-3xl font-bold mb-8 text-gray-800 text-center">Mobile Devices</h2>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
            <div class="product-card bg-white p-6 rounded-xl shadow-lg flex flex-col justify-between">
                <div class="text-center">
                    <!-- Placeholder Image -->
                    <img class="w-full h-48 object-cover rounded-lg mb-4" 
                         src="<?= get_product_image_url($product['image_url'], $product['name']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>">
                    
                    <h3 class="text-xl font-semibold mb-2 text-gray-900"><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="text-2xl font-bold text-green-600 mb-4">$<?= number_format($product['price'], 2) ?></p>
                    <p class="text-gray-600 text-sm mb-4"><?= htmlspecialchars($product['description']) ?></p>
                </div>
                
                <div class="mt-auto">
                    <?php if ($is_logged_in): ?>
                        <form action="add_to_cart.php" method="POST" class="w-full">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <button type="submit" name="add_to_cart" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                                Add to Cart
                            </button>
                        </form>
                    <?php else: ?>
                        <p class="text-red-500 text-center text-sm font-medium">Please <a href="login.php" class="underline hover:text-red-700">log in</a> to purchase.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="col-span-4 text-center text-gray-600">No products found in the database.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
<?php
// Close connection
mysqli_close($conn);
?>
