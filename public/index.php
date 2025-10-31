<?php
session_start();

// ✅ SESSION TIMEOUT (10 minutes = 600 seconds)
$timeout_duration = 600;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

// ✅ CHECK IF USER IS LOGGED IN
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

// ✅ PREVENT BACK BUTTON AFTER LOGOUT - ENHANCED
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// ✅ Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ✅ Handle Add to Cart action
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);

    if (!isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = 1;
    } else {
        $_SESSION['cart'][$product_id]++;
    }

    header("Location: index.php");
    exit;
}

// ✅ Product list (static demo)
$products = [
    ["id" => 1, "name" => "iPhone 14 Pro", "price" => 119999, "img" => "https://m.media-amazon.com/images/I/71geVdy6-OS._SX679_.jpg"],
    ["id" => 2, "name" => "Samsung Galaxy S23 Ultra", "price" => 124999, "img" => "https://m.media-amazon.com/images/I/61vGQNUEsGL._SX679_.jpg"],
    ["id" => 3, "name" => "MacBook Air M2", "price" => 109999, "img" => "https://m.media-amazon.com/images/I/61NI1FZ3bqL._SX679_.jpg"],
    ["id" => 4, "name" => "Sony WH-1000XM5", "price" => 29990, "img" => "https://m.media-amazon.com/images/I/81pJk4b2bdL._SX679_.jpg"],
    ["id" => 5, "name" => "Apple Watch Series 9", "price" => 45900, "img" => "https://m.media-amazon.com/images/I/61-PblYntsL._SX679_.jpg"],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GadgetHut - Electronics Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-100">

<!-- 🔹 Navbar -->
<nav class="bg-gray-900 text-white p-4 flex justify-between items-center shadow-lg">
    <h1 class="text-2xl font-bold">GadgetHut</h1>
    <div class="flex items-center gap-4">
        <span class="text-gray-300">Welcome, <strong><?= htmlspecialchars($_SESSION["username"]); ?></strong></span>
        <a href="cart.php" class="relative bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg font-semibold transition">
            🛒 Cart
            <?php if (!empty($_SESSION['cart'])): ?>
                <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full px-2 py-0.5">
                    <?= array_sum($_SESSION['cart']); ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg font-semibold transition">Logout</a>
    </div>
</nav>

<!-- 🔹 Product Grid -->
<main class="flex-grow container mx-auto px-6 py-10">
    <h2 class="text-3xl font-bold mb-8 text-gray-800 text-center">Explore Our Latest Electronics</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <?php foreach ($products as $p): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:scale-105 transition">
                <img src="<?= htmlspecialchars($p['img']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-56 object-cover">
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2"><?= htmlspecialchars($p['name']) ?></h3>
                    <p class="font-bold text-green-600 mb-4">₹<?= number_format($p['price']) ?></p>
                    <form method="POST" action="">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold">
                            Add to Cart
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<footer class="bg-gray-900 text-white text-center py-4 mt-10">
    &copy; <?= date("Y") ?> GadgetHut Store | All Rights Reserved
</footer>

</body>
</html>