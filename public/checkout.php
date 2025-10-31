<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "db_config.php";

$user_id = $_SESSION['id'];
$cart_items = [];
$total_cost = 0;
$checkout_status = $_GET['action'] ?? ''; // Check for 'complete' or 'remove' actions

// --- Handle Checkout Action ---
if ($checkout_status === 'complete') {
    // 1. Simulate Order Processing (e.g., move cart to 'orders' table - skipped for brevity)
    
    // 2. Clear the Cart
    $sql_clear = "DELETE FROM cart WHERE user_id = ?";
    if ($stmt_clear = $conn->prepare($sql_clear)) {
        $stmt_clear->bind_param("i", $user_id);
        $stmt_clear->execute();
        $stmt_clear->close();
    }
    
    // Redirect to show the "Thank You" message
    header("location: checkout.php?status=success");
    exit;
}

// --- Handle Remove Item Action ---
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['item_id'])) {
    $item_id = intval($_GET['item_id']);
    $sql_remove = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    if ($stmt_remove = $conn->prepare($sql_remove)) {
        $stmt_remove->bind_param("ii", $item_id, $user_id);
        $stmt_remove->execute();
        $stmt_remove->close();
    }
    // Redirect back to clean URL
    header("location: checkout.php");
    exit;
}

// --- Fetch Cart Items (Default View) ---
$sql_cart = "
    SELECT 
        c.id as cart_item_id, p.name, p.price, c.quantity
    FROM 
        cart c
    JOIN 
        products p ON c.product_id = p.id
    WHERE 
        c.user_id = ?
";

if ($stmt = $conn->prepare($sql_cart)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $subtotal = $row['price'] * $row['quantity'];
        $total_cost += $subtotal;
        $row['subtotal'] = $subtotal;
        $cart_items[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-Commerce Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], },
                    colors: { 'primary': '#4f46e5', }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">
    
    <!-- Navigation Bar (Minimal for Checkout) -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="flex-shrink-0 flex items-center">
                    <i class="fas fa-mobile-alt text-2xl text-primary mr-2"></i>
                    <span class="text-2xl font-bold text-gray-900">M-Shop</span>
                </a>
                <a href="logout.php" class="px-4 py-2 text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <!-- Thank You Screen -->
            <div class="bg-white p-8 rounded-xl shadow-2xl text-center border-t-4 border-green-500">
                <i class="fas fa-check-circle text-6xl text-green-500 mb-4"></i>
                <h1 class="text-4xl font-extrabold text-gray-900 mb-2">
                    Thanks for shopping, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                </h1>
                <p class="text-xl text-gray-600">Your order has been processed successfully.</p>
                <div class="mt-8 space-x-4">
                    <a href="index.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-primary hover:bg-indigo-700 transition duration-150">
                        Continue Shopping
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- Checkout Cart View -->
            <h1 class="text-3xl font-extrabold text-gray-900 mb-8 text-center sm:text-left">
                Your Shopping Cart
            </h1>

            <?php if (count($cart_items) > 0): ?>
                
                <!-- Cart Items List -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($cart_items as $item): ?>
                            <li class="flex justify-between items-center py-4">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center">
                                    <span class="text-lg font-medium text-gray-900 w-full sm:w-64 truncate">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </span>
                                    <span class="text-sm text-gray-500 mt-1 sm:mt-0 sm:ml-4">
                                        Qty: <?php echo $item['quantity']; ?> x $<?php echo number_format($item['price'], 2); ?>
                                    </span>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <span class="text-lg font-bold text-primary">
                                        $<?php echo number_format($item['subtotal'], 2); ?>
                                    </span>
                                    <a href="checkout.php?action=remove&item_id=<?php echo $item['cart_item_id']; ?>" 
                                       onclick="return confirm('Are you sure you want to remove this item?');" 
                                       class="text-red-500 hover:text-red-700 transition duration-150">
                                        <i class="fas fa-times-circle"></i>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Summary and Checkout Button -->
                <div class="bg-white rounded-xl shadow-lg p-6 border-t border-gray-200">
                    <div class="flex justify-between items-center text-2xl font-bold mb-6">
                        <span>Order Total:</span>
                        <span class="text-primary">$<?php echo number_format($total_cost, 2); ?></span>
                    </div>

                    <a href="checkout.php?action=complete" onclick="return confirm('Confirm purchase of $<?php echo number_format($total_cost, 2); ?>?')"
                       class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-lg text-lg font-medium text-white bg-green-600 hover:bg-green-700 transition duration-150 ease-in-out">
                        <i class="fas fa-wallet mr-2"></i> Proceed to Digital Payment & Checkout
                    </a>

                    <a href="index.php" class="mt-4 w-full flex justify-center py-2 text-sm font-medium rounded-lg text-primary hover:text-indigo-700 transition duration-150">
                        Continue Shopping
                    </a>
                </div>

            <?php else: ?>
                <!-- Empty Cart Message -->
                <div class="bg-white p-12 rounded-xl shadow-lg text-center">
                    <i class="fas fa-shopping-cart text-6xl text-gray-400 mb-4"></i>
                    <h2 class="text-2xl font-semibold text-gray-700 mb-4">
                        Your cart is empty!
                    </h2>
                    <p class="text-gray-500 mb-6">
                        Looks like you haven't added any mobile tech yet.
                    </p>
                    <a href="index.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-primary hover:bg-indigo-700 transition duration-150">
                        Start Shopping
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

</body>
</html>
