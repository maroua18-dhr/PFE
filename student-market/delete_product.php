<?php
session_start();
require 'config/database.php';

if (isset($_GET['id']) && isset($_SESSION['user'])) {
    $product_id = $_GET['id'];
    $user_id = $_SESSION['user']['id'];

    // Security: Only delete if the product belongs to the logged-in user
    $sql = "DELETE FROM products WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$product_id, $user_id]);
}

header("Location: profile.php");
exit;