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
$produitsParPage = 8; // Nombre de produits par page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $produitsParPage;

// ✅ Récupérer les produits avec LIMIT et OFFSET
// ✅ Récupérer les produits avec LIMIT, OFFSET et tri aléatoire
try {
    $stmt = $pdo->prepare("SELECT * FROM Products LIMIT :offset, :limit");
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



// Récupérer les catégories
$categoriesStmt = $pdo->query("SELECT * FROM Category");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les produits avec les catégories
$stmt = $pdo->prepare("SELECT Products.*, Category.name AS category_name 
                       FROM Products
                       JOIN Category ON Products.idCategory = Category.idCategory
                        LIMIT :offset, :limit");
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $produitsParPage, PDO::PARAM_INT);
$stmt->execute();
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
<a href="accueil.php" class="flex items-center">
        <img src="images/logo-removebg.png" alt="Logo" class="h-auto" style="max-height: 80px; margin-left: 8px; transition: transform 0.2s; transform: scale(1);">
    </a>    
    <!-- Barre de recherche -->
    <form method="get" class="ml-16 flex items-center relative" style="width: 60%;">
    <input
        type="text"
        id="search"
        name="search"
        placeholder="Rechercher des produits..."
        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 text-black focus:ring-[#176b61]"
        onkeyup="fetchSuggestions(this.value)"
    >
    <!-- Crois pour supprimer le texte -->
    <button type="button" id="clearSearch" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700" style="border: none; background: none;" onclick="clearSearch()">
    <i class="fas fa-times"></i>
</button>
    <!-- Suggestions affichées sous l'input -->
    <div id="suggestions" class="hidden"></div>
</form>

</header>


<div class="container mx-auto my-8 flex">


    <!-- Colonne des catégories à gauche -->
    <div class="w-1/5 p-4 bg-white shadow-md rounded-lg mr-2">
    <h3 class="text-xl font-bold mb-4">Catégories</h3>
    <ul>
        <?php foreach ($categories as $categorie): ?>
            <li class="mb-2">
                <!-- Lien vers la page des catégories avec l'ID de la catégorie -->
                <a href="category_page.php?id=<?php echo $categorie['idCategory']; ?>" class="text-gray-600 hover:text-gray-900 hover:underline">
                    <?php echo htmlspecialchars($categorie['name']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
    <!-- Colonne des produits à droite -->
    <div class="w-4/5">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <?php if (!empty($produits)): ?>
<?php foreach ($produits as $produit): ?>
    <a href="product_details.php?id=<?php echo $produit['idProduct']; ?>" class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col">
        <img src="<?php echo !empty($produit['img']) ? htmlspecialchars($produit['img']) : 'images/default.png'; ?>" 
             alt="<?php echo !empty($produit['name']) ? htmlspecialchars($produit['name']) : 'Produit'; ?>" 
             class="w-full h-36 mb-2 object-cover">
        <h3 class="text-l hover:underline m-2"><?php echo htmlspecialchars($produit['name']); ?></h3>
    </a>
<?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-gray-500">Aucun produit disponible pour le moment.</p>
        <?php endif; ?>
    </div>
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
<!-- Superposition de fond gris -->
<div id="overlay" class="hidden fixed inset-0 bg-gray-500 opacity-50 z-10"></div>
<script>

function fetchSuggestions(query) {
    if (query.length === 0) {
        document.getElementById("suggestions").innerHTML = "";
        document.getElementById("suggestions").classList.add("hidden");
        document.getElementById("suggestions").style.display = "none"; // Ajout de cette ligne
        document.getElementById("overlay").classList.add("hidden");
        return;
    }
    
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_suggestions.php?query=" + encodeURIComponent(query), true);
    xhr.onload = function() {
        if (this.status === 200) {
            try {
                const suggestions = JSON.parse(this.responseText);
                let html = "";
                
                if (suggestions.length > 0) {
                    suggestions.forEach((item, index) => {
                        html += `<div onclick="selectSuggestion(${item.idProduct}, '${item.name}')"
                        style="padding: 10px; cursor: pointer; border-bottom: 1px solid #ddd;">
                            ${item.name}
                        </div>`;
                    });
                    document.getElementById("overlay").classList.remove("hidden");
                } else {
                    html = `<div>Aucun résultat trouvé pour la recherche : <strong>${query}</strong></div>`;
                    document.getElementById("overlay").classList.remove("hidden");
                }
                
                document.getElementById("suggestions").innerHTML = html;
                document.getElementById("suggestions").style.display = "block";
                document.getElementById("suggestions").classList.remove("hidden");
            } catch (e) {
                console.error("Erreur de parsing JSON : ", e);
            }
        }
    };
    xhr.onerror = function() {
        document.getElementById("suggestions").innerHTML = "<div>Network error</div>";
        document.getElementById("suggestions").classList.remove("hidden");
        document.getElementById("overlay").classList.remove("hidden");
    };
    xhr.send();
}

function selectSuggestion(id, name) {
    document.getElementById("search").value = name;
    document.getElementById("suggestions").innerHTML = "";
    document.getElementById("suggestions").classList.add("hidden");
    document.getElementById("suggestions").style.display = "none"; // Ajout de cette ligne
    document.getElementById("overlay").classList.add("hidden");
    window.location.href = 'product_details.php?id=' + encodeURIComponent(id);
}



// Fonction pour effacer le texte dans le champ de recherche et masquer les suggestions
function clearSearch() {
        const searchInput = document.getElementById("search");
        const suggestionsDiv = document.getElementById("suggestions");
        const overlay = document.getElementById("overlay");

        searchInput.value = ''; // Vider le champ de recherche
        suggestionsDiv.innerHTML = ''; // Vider les suggestions
        suggestions.style.display = "none";
        suggestionsDiv.classList.add("hidden"); // Masquer les suggestions
        overlay.classList.add("hidden"); // Masquer l'overlay
    }


    document.addEventListener('DOMContentLoaded', function() {
    const clearButton = document.getElementById('clearSearch');

    clearButton.addEventListener('click', clearSearch);
});
</script>

</body>
</html>








