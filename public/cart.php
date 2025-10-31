<?php
session_start();

// ✅ SESSION TIMEOUT (10 minutes)
$timeout_duration = 600;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

// ✅ Check login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

// ✅ Prevent back navigation after logout
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// ✅ Connect DB (if you need it later)
require_once __DIR__ . '/db_config.php';

// ✅ Demo product data (same as index.php)
$products = [
    1 => ["name" => "iPhone 14 Pro", "price" => 119999, "img" => "https://m.media-amazon.com/images/I/71geVdy6-OS._SX679_.jpg"],
    2 => ["name" => "Samsung Galaxy S23 Ultra", "price" => 124999, "img" => "https://m.media-amazon.com/images/I/61vGQNUEsGL._SX679_.jpg"],
    3 => ["name" => "MacBook Air M2", "price" => 109999, "img" => "https://m.media-amazon.com/images/I/61NI1FZ3bqL._SX679_.jpg"],
    4 => ["name" => "Sony WH-1000XM5", "price" => 29990, "img" => "https://m.media-amazon.com/images/I/81pJk4b2bdL._SX679_.jpg"],
    5 => ["name" => "Apple Watch Series 9", "price" => 45900, "img" => "https://m.media-amazon.com/images/I/61-PblYntsL._SX679_.jpg"]
];

// ✅ Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ✅ Handle quantity updates
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['update_qty'])) {
        $pid = intval($_POST['product_id']);
        $qty = max(1, intval($_POST['quantity']));
        $_SESSION['cart'][$pid] = $qty;
    }

    if (isset($_POST['remove_item'])) {
        $pid = intval($_POST['product_id']);
        unset($_SESSION['cart'][$pid]);
    }

    if (isset($_POST['checkout'])) {
        // Here you can later insert checkout logic (orders table, etc.)
        $_SESSION['cart'] = [];
        echo "<script>alert('✅ Order placed successfully!'); window.location='index.php';</script>";
        exit;
    }

    header("Location: cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart - GadgetHut</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<!-- 🔹 Navbar -->
<nav class="bg-gray-900 text-white p-4 flex justify-between items-center shadow-lg">
    <h1 class="text-2xl font-bold">🛒 Your Cart</h1>
    <div class="flex items-center gap-4">
        <a href="index.php" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg font-semibold">Continue Shopping</a>
        <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg font-semibold">Logout</a>
    </div>
</nav>

<!-- 🔹 Cart Content -->
<main class="container mx-auto p-8 flex-grow">
    <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">Your Shopping Cart</h2>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="text-center text-gray-600 text-lg mt-10">
            🛍️ Your cart is empty.<br>
            <a href="index.php" class="text-blue-600 hover:underline">Start shopping now</a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <form method="POST">
                <table class="min-w-full bg-white rounded-xl shadow-lg overflow-hidden">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="py-3 px-4 text-left">Product</th>
                            <th class="py-3 px-4 text-left">Price</th>
                            <th class="py-3 px-4 text-left">Quantity</th>
                            <th class="py-3 px-4 text-left">Subtotal</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        foreach ($_SESSION['cart'] as $pid => $qty): 
                            $item = $products[$pid];
                            $subtotal = $item['price'] * $qty;
                            $total += $subtotal;
                        ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4 flex items-center gap-3">
                                <img src="<?= htmlspecialchars($item['img']) ?>" class="w-16 h-16 rounded object-cover">
                                <span class="font-semibold"><?= htmlspecialchars($item['name']) ?></span>
                            </td>
                            <td class="py-3 px-4">₹<?= number_format($item['price']) ?></td>
                            <td class="py-3 px-4">
                                <input type="number" name="quantity" value="<?= $qty ?>" min="1"
                                       class="w-20 border border-gray-300 rounded-lg text-center py-1">
                                <input type="hidden" name="product_id" value="<?= $pid ?>">
                            </td>
                            <td class="py-3 px-4 font-semibold">₹<?= number_format($subtotal) ?></td>
                            <td class="py-3 px-4 text-center">
                                <button type="submit" name="update_qty" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded mr-2">Update</button>
                                <button type="submit" name="remove_item" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">Remove</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="flex justify-between items-center mt-6">
                    <h3 class="text-xl font-bold">Total: ₹<?= number_format($total) ?></h3>
                    <button type="submit" name="checkout" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                        Proceed to Checkout
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</main>

<footer class="bg-gray-900 text-white text-center py-4 mt-10">
    &copy; <?= date("Y") ?> GadgetHut Store | All Rights Reserved
</footer>

</body>
</html>

