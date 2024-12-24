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
// ✅ Paramètres de pagination
$produitsParPage = 6; // Nombre de produits par page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $produitsParPage;

// ✅ Récupérer les produits avec LIMIT et OFFSET
// ✅ Récupérer les produits avec LIMIT, OFFSET et tri aléatoire
try {
    $stmt = $pdo->prepare("SELECT * FROM Products ORDER BY RAND() LIMIT :offset, :limit");
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $produitsParPage, PDO::PARAM_INT);
    $stmt->execute();
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Compter le nombre total de produits pour la pagination
    $totalProduits = $pdo->query("SELECT COUNT(*) FROM Products")->fetchColumn();
    $totalPages = ceil($totalProduits / $produitsParPage);
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
<header class="bg-white px-4 flex items-center shadow-md" style="height: 80px;">
    <img src="images/logo-removebg.png" alt="Logo" class="h-full ml-2 object-contain">
    
    <!-- Barre de recherche -->
    <form method="get" class="ml-8 flex items-center relative" style="width: 75%;">
    <input
        type="text"
        id="search"
        name="search"
        placeholder="Rechercher des produits..."
        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 text-black focus:ring-[#176b61]"
        onkeyup="fetchSuggestions(this.value)"
    >
    <!-- Suggestions affichées sous l'input -->
    <div id="suggestions" class="hidden"></div>
</form>
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
</p>                        <button class="custom-button">Voir Détails</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-gray-500">Aucun produit disponible pour le moment.</p>
        <?php endif; ?>
    </div>
</div>


<!-- ✅ Pagination -->
<div class="flex justify-center mt-8">
    <nav aria-label="Pagination">
        <ul class="inline-flex space-x-2">
            <?php if ($page > 1): ?>
                <li>
                    <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Précédent</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li>
                    <a href="?page=<?php echo $i; ?>" class="px-4 py-2 <?php echo $i === $page ? 'bg-[#176b61] text-white' : 'bg-gray-200 text-gray-700'; ?> rounded hover:bg-gray-300">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li>
                    <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Suivant</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
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


<script>

function fetchSuggestions(query) {
    if (query.length === 0) {
        document.getElementById("suggestions").innerHTML = "";
        document.getElementById("suggestions").classList.add("hidden");
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_suggestions.php?query=" + encodeURIComponent(query), true);
    xhr.onload = function() {
    if (this.status === 200) {
        try {
            const suggestions = JSON.parse(this.responseText); // Parse la réponse JSON
            console.log(suggestions); // Affiche les suggestions dans la console pour vérification

            let html = "";
            suggestions.forEach(item => {
                html += `<div onclick="selectSuggestion(${item.idProduct}, '${item.name}')">${item.name}</div>`;
            });

            // Affiche les suggestions dans l'élément #suggestions
            document.getElementById("suggestions").innerHTML = html;
            document.getElementById("suggestions").style.display = "block"; // Forcer l'affichage
            document.getElementById("suggestions").classList.remove("hidden");
            console.log("Suggestions mises à jour et affichées");
            console.log(document.getElementById("suggestions").style);  // Affiche les styles appliqués à l'élément            
        } catch (e) {
            console.error("Erreur de parsing JSON : ", e);
        }
    }
};
    xhr.onerror = function() {
        document.getElementById("suggestions").innerHTML = "<div>Network error</div>";
        document.getElementById("suggestions").classList.remove("hidden");
    };
    xhr.send();
}

function selectSuggestion(id, name) {
    document.getElementById("search").value = name;
    document.getElementById("suggestions").innerHTML = "";
    document.getElementById("suggestions").classList.add("hidden");
    window.location.href = 'product_details.php?id=' + encodeURIComponent(id);
}


</script>