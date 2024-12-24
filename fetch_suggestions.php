<?php
// Get search query
$query = isset($_GET['query']) ? $_GET['query'] : '';

// Connect to the database
$pdo = new PDO("mysql:host=localhost;dbname=catalogue", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch product names matching the query
$stmt = $pdo->prepare("SELECT idProduct, name FROM Products WHERE name LIKE :query LIMIT 10");
$stmt->execute(['query' => '%' . $query . '%']);
$suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ensure that the response is valid JSON
header('Content-Type: application/json');
echo json_encode($suggestions);
?>