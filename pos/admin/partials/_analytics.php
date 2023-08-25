<?php
//1. Customers
$query = "SELECT COUNT(*) FROM `rpos_customers` ";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($customers);
$stmt->fetch();
$stmt->close();

//2. Orders
$query = "SELECT COUNT(*) FROM `rpos_orders` ";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($orders);
$stmt->fetch();
$stmt->close();

//3. Orders
$query = "SELECT COUNT(*) FROM `rpos_products` ";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($products);
$stmt->fetch();
$stmt->close();

//sales
//4. Get today's date
$timezone = new DateTimeZone("Asia/Manila");
$date = new DateTime("now", $timezone);
$currentDate = $date->format("Y-m-d");

// Calculate the start and end dates for the current day
$startOfDay = $currentDate . " 00:00:00";
$endOfDay = $currentDate . " 23:59:59";

// Query to calculate the sum of sales for the current day
$query = "SELECT SUM(pay_amt) AS total_sales FROM rpos_payments WHERE created_at >= ? AND created_at <= ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ss", $startOfDay, $endOfDay);
$stmt->execute();
$stmt->bind_result($totalSales);
$stmt->fetch();
$stmt->close();

// Check if total sales exist for the current day
if ($totalSales !== null && $totalSales > 0) {
    // Check if the sales entry already exists for the current day
    $checkQuery = "SELECT * FROM sales WHERE created_at = ?";
    $checkStmt = $mysqli->prepare($checkQuery);
    $checkStmt->bind_param("s", $currentDate);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Update the existing sales entry for the current day
        $updateQuery = "UPDATE sales SET eod_sales = ? WHERE created_at = ?";
        $updateStmt = $mysqli->prepare($updateQuery);
        $updateStmt->bind_param("ds", $totalSales, $currentDate);
        $updateStmt->execute();
        $updateStmt->close();
    } else {
        // Insert a new sales entry for the current day
        $insertQuery = "INSERT INTO sales (eod_sales, created_at) VALUES (?, ?)";
        $insertStmt = $mysqli->prepare($insertQuery);
        $insertStmt->bind_param("ds", $totalSales, $currentDate);
        $insertStmt->execute();
        $insertStmt->close();
    }

    $checkStmt->close();
}


