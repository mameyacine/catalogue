<?php
// Afficher les erreurs PHP pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=localhost;dbname=catalogue", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

$produitsParPage = 8; // Nombre de produits par page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$categoryId = isset($_GET['category_id']) && is_numeric($_GET['category_id']) ? intval($_GET['category_id']) : null;
$offset = ($page - 1) * $produitsParPage;

// Récupérer la liste des catégories
try {
    $stmt_categories = $pdo->prepare("SELECT * FROM Category");
    $stmt_categories->execute();
    $categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des catégories : " . $e->getMessage());
}

// Si une catégorie est sélectionnée, afficher ses produits
$produits = [];
$totalProduits = 0;
if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {
    $categoryId = $_GET['category_id'];

    // Récupérer les produits de la catégorie
    try {
        $stmt_produits = $pdo->prepare("SELECT * FROM Products WHERE idCategory = :idCategory LIMIT :offset, :produitsParPage");
        $stmt_produits->bindParam(':idCategory', $categoryId, PDO::PARAM_INT);
        $stmt_produits->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt_produits->bindParam(':produitsParPage', $produitsParPage, PDO::PARAM_INT);
        $stmt_produits->execute();
        $produits = $stmt_produits->fetchAll(PDO::FETCH_ASSOC);

        // Compter le nombre total de produits dans cette catégorie
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM Products WHERE idCategory = :idCategory");
        $stmt_count->bindParam(':idCategory', $categoryId, PDO::PARAM_INT);
        $stmt_count->execute();
        $totalProduits = $stmt_count->fetchColumn();
    } catch (PDOException $e) {
        die("Erreur lors de la récupération des produits : " . $e->getMessage());
    }
} else {
    // Si aucune catégorie n'est sélectionnée, afficher tous les produits
    try {
        $stmt_produits = $pdo->prepare("SELECT * FROM Products LIMIT :offset, :produitsParPage");
        $stmt_produits->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt_produits->bindParam(':produitsParPage', $produitsParPage, PDO::PARAM_INT);
        $stmt_produits->execute();
        $produits = $stmt_produits->fetchAll(PDO::FETCH_ASSOC);

        // Compter le nombre total de produits
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM Products");
        $stmt_count->execute();
        $totalProduits = $stmt_count->fetchColumn();
    } catch (PDOException $e) {
        die("Erreur lors de la récupération des produits : " . $e->getMessage());
    }
}

// Calculer le nombre total de pages
$totalPages = ceil($totalProduits / $produitsParPage);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégories</title>
    <link href="public/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<!-- Header -->
<header class="bg-white px-4 flex items-center shadow-md" style="height: 80px;">
    <img src="images/logo-removebg.png" alt="Logo" class="h-full ml-2 object-contain">
</header>

<!-- Conteneur principal -->
<div class="container mx-auto my-8 flex flex-col md:flex-row gap-8">
    <!-- Catégories (colonne de gauche) -->
    <div class="w-full md:w-1/4 bg-white p-6 rounded-lg shadow-md space-y-4">
        <?php foreach ($categories as $category): ?>
            <a href="?category_id=<?php echo $category['idCategory']; ?>" class="block p-4 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-300">
                <h3 class="text-xl"><?php echo htmlspecialchars($category['name']); ?></h3>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Produits (colonne de droite) -->
    <div class="w-full md:w-3/4">
        <?php if (!empty($produits)): ?>
            <h2 class="text-3xl font-bold mb-6">Produits</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                <?php foreach ($produits as $produit): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <a href="product_details.php?id=<?php echo $produit['idProduct']; ?>" class="block">
                            <img src="<?php echo !empty($produit['img']) ? htmlspecialchars($produit['img']) : 'images/default.png'; ?>" 
                                 alt="<?php echo htmlspecialchars($produit['name']); ?>" 
                                 class="w-full h-48 object-cover">
                            <div class="p-4">
                                <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($produit['name']); ?></h3>
                                <p class="text-gray-500"><?php echo htmlspecialchars($produit['description']); ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <!-- Pagination -->
<div class="flex justify-center mt-8">
    <nav aria-label="Pagination">
        <ul class="inline-flex space-x-2">
            <?php if ($page > 1): ?>
                <li>
                    <a href="?page=<?php echo $page - 1; ?>&category_id=<?php echo $categoryId; ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Précédent</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li>
                    <a href="?page=<?php echo $i; ?>&category_id=<?php echo $categoryId; ?>" class="px-4 py-2 <?php echo $i === $page ? 'bg-[#176b61] text-white' : 'bg-gray-200 text-gray-700'; ?> rounded hover:bg-gray-300">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li>
                    <a href="?page=<?php echo $page + 1; ?>&category_id=<?php echo $categoryId; ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Suivant</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
    </div>
</div>

<!-- Footer -->
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