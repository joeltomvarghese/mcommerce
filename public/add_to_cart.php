<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ✅ Include correct database config path
require_once __DIR__ . '/../includes/db_config.php';

// ✅ Handle POST request
if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $user_id = $_SESSION['user_id'];

    // ✅ Check if product already in cart
    $check = mysqli_prepare($conn, "SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
    mysqli_stmt_bind_param($check, "ii", $user_id, $product_id);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        echo "<script>alert('Product already in your cart!'); window.location.href='index.php';</script>";
    } else {
        // ✅ Insert into cart
        $stmt = mysqli_prepare($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Product added to cart successfully!'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Database error while adding product.'); window.location.href='index.php';</script>";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_stmt_close($check);
} else {
    echo "<script>alert('Invalid product request.'); window.location.href='index.php';</script>";
}

mysqli_close($conn);
?>
