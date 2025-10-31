<?php
session_start();
if (!isset($_SESSION["loggedin"])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
<div class="bg-white shadow-lg p-8 rounded-xl text-center">
    <h1 class="text-3xl font-bold mb-4">✅ Order Placed Successfully!</h1>
    <p class="text-gray-700 mb-6">Thank you for shopping with GadgetHut.</p>
    <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Back to Store</a>
</div>
</body>
</html>
