<?php
// Connexion à la base de données
$pdo = new PDO("mysql:host=localhost;dbname=catalogue", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer le terme de recherche
$query = isset($_GET['query']) ? $_GET['query'] : '';
$excludeId = isset($_GET['exclude']) ? (int)$_GET['exclude'] : 0; // ID à exclure

// Séparer les mots de la recherche
$searchTerms = explode(' ', trim($query));

// Construire la requête SQL dynamiquement
$sql = "SELECT idProduct, name FROM Products WHERE 1=1";
$params = [];

if ($excludeId > 0) {
    $sql .= " AND idProduct != :excludeId"; // Exclure le produit
    $params[':excludeId'] = $excludeId;
}

foreach ($searchTerms as $key => $term) {
    $paramName = ":term" . $key;
    $sql .= " AND name LIKE " . $paramName;
    $params[$paramName] = '%' . $term . '%';
}

$sql .= " LIMIT 10";

// Exécuter la requête
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retourner une réponse JSON
header('Content-Type: application/json');
echo json_encode($suggestions);
?>