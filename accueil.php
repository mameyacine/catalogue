<?php
// Afficher les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO("mysql:host=localhost;dbname=catalogue", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}


// ✅ Récupérer les produits depuis la base de données
try {
    $stmt = $pdo->query("SELECT * FROM Products");
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur lors de la récupération des produits : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue de Produits</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="public/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<!-- ✅ Header -->
<header class="bg-[#176b61] text-white py-4 flex items-center shadow-md">
    <img src="images/logo.png" alt="Logo" class="h-20 w-20 ml-4">
    <h1 class="text-3xl font-bold ml-4">Catalogue de Produits</h1>
</header>

<!-- ✅ Catalogue de produits -->
<div class="container mx-auto my-8">
    <h2 class="text-2xl font-bold text-center mb-6">Nos Produits</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php if (!empty($produits)): ?>
            <?php foreach ($produits as $produit): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="<?php echo !empty($produit['img']) ? htmlspecialchars($produit['img']) : 'images/default.png'; ?>" 
     alt="<?php echo !empty($produit['nom']) ? htmlspecialchars($produit['nom']) : 'Produit'; ?>" 
     class="w-full h-48 object-cover">                    <div class="p-4">
                        <h3 class="text-xl font-bold"><?php echo htmlspecialchars($produit['name']); ?></h3>
                        <p class="text-gray-600">
    <?php echo !empty($produit['description']) ? htmlspecialchars($produit['description']) : 'Aucune description disponible.'; ?>
</p>                        <button class="mt-4 bg-[#176b61] text-white px-4 py-2 rounded hover:bg-[#145b50]">Voir Détails</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-gray-500">Aucun produit disponible pour le moment.</p>
        <?php endif; ?>
    </div>
</div>
<!-- ✅ Footer -->
<footer class="site-footer text-white py-6 mt-12 bg-[#176b61]">
    <div class="container mx-auto text-center">
        <h6 class="font-bold text-lg mb-2">Contact</h6>
        <ul class="space-y-2 mb-4">
            <li><a href="#" class="text-white hover:underline">À Propos</a></li>
            <li><a href="#" class="text-white hover:underline">Contact</a></li>
            <li><a href="#" class="text-white hover:underline">Contribuer</a></li>
            <li><a href="#" class="text-white hover:underline">Politique de Confidentialité</a></li>
            <li><a href="#" class="text-white hover:underline">Plan du Site</a></li>
        </ul>
        <div class="flex justify-center space-x-4 mt-4">
            <a href="#" class="text-blue-500 hover:text-blue-400"><i class="fab fa-facebook text-2xl"></i></a>
            <a href="#" class="text-blue-300 hover:text-blue-200"><i class="fab fa-twitter text-2xl"></i></a>
            <a href="#"  class="text-blue-700 hover:text-blue-600"><i class="fab fa-linkedin text-2xl"></i></a>

        </div>
        <p class="mt-4 text-sm">&copy; <?php echo date('Y'); ?> Catalogue. Tous droits réservés.</p>
    </div>
</footer>

</body>
</html>