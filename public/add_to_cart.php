<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "db_config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
    $user_id = $_SESSION['id'];
    $product_id = intval($_POST['product_id']);
    $quantity = 1; // Default to adding one item

    // Check if the item is already in the cart
    $sql_check = "SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?";
    
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param("ii", $user_id, $product_id);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows == 1) {
            // Item exists: update the quantity
            $stmt_check->bind_result($current_quantity);
            $stmt_check->fetch();
            $new_quantity = $current_quantity + 1;

            $sql_update = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
            if ($stmt_update = $conn->prepare($sql_update)) {
                $stmt_update->bind_param("iii", $new_quantity, $user_id, $product_id);
                $stmt_update->execute();
                $stmt_update->close();
            }
        } else {
            // Item does not exist: insert new item
            $sql_insert = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
            if ($stmt_insert = $conn->prepare($sql_insert)) {
                $stmt_insert->bind_param("iii", $user_id, $product_id, $quantity);
                $stmt_insert->execute();
                $stmt_insert->close();
            }
        }
        $stmt_check->close();
    }
    
    $conn->close();
    
    // Redirect back to the home page with a success anchor
    header("location: index.php?status=added#product-listing");
    exit;

} else {
    // If accessed directly without POST, redirect to home
    header("location: index.php");
    exit;
}
?>
