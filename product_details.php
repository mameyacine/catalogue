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

// Vérifier si l'ID du produit est passé en paramètre
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idProduit = $_GET['id'];

    // Récupérer les détails du produit
    try {
        $stmt = $pdo->prepare("SELECT * FROM Products WHERE idProduct = :id");
        $stmt->bindParam(':id', $idProduit, PDO::PARAM_INT);
        $stmt->execute();
        $produit = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$produit) {
            die("Produit introuvable.");
        }

        // Récupérer des produits similaires (par catégorie par exemple)
        $category = $produit['idCategory'];
        $stmt_similaires = $pdo->prepare("SELECT * FROM Products WHERE idCategory = :idCategory AND idProduct != :id");
        $stmt_similaires->bindParam(':idCategory', $category, PDO::PARAM_INT);
        $stmt_similaires->bindParam(':id', $idProduit, PDO::PARAM_INT);
        $stmt_similaires->execute();
        $produitsSimilaires = $stmt_similaires->fetchAll(PDO::FETCH_ASSOC);

        if (empty($produitsSimilaires)) {
            echo "Aucun produit similaire trouvé.";
        }

    } catch (PDOException $e) {
        die("Erreur lors de la récupération du produit : " . $e->getMessage());
    }
} else {
    die("ID du produit invalide.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Produit</title>
    <link href="public/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<!-- Header -->
<header class="bg-white px-4 flex items-center shadow-md" style="height: 80px;">
    <img src="images/logo-removebg.png" alt="Logo" class="h-full ml-2 object-contain">
    
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


<!-- Détails du produit -->
<div class="container mx-auto my-8">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Image du produit -->
        <div class="w-full md:w-1/2">
    <img src="<?php echo htmlspecialchars($produit['img'] ?? 'images/default.png'); ?>" 
         alt="<?php echo htmlspecialchars($produit['name']); ?>" 
         class="w-full h-96 object-cover rounded-lg">
</div>

        <!-- Informations détaillées du produit -->
        <div class="w-full md:w-1/2">
            <h2 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($produit['name']); ?></h2>
            <p class="text-xl text-gray-700 mb-4"><?php echo htmlspecialchars($produit['description']); ?></p>
        </div>
    </div>
</div>

<div class="container mx-auto my-8 relative">
<h1 class="text-2xl font-bold mb-4">Produits similaires</h1>

    <!-- Flèche gauche -->
    <button class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-3 rounded-full hover:bg-gray-600 focus:outline-none z-10" aria-label="Précédent">
        <i class="fas fa-chevron-left"></i>
    </button>

    <!-- Carrousel -->
    <div class="overflow-hidden">
        <div class="flex space-x-6 transition-transform duration-300">
            <?php foreach ($produitsSimilaires as $produitSimilaire): ?>
                <div class="flex-shrink-0 w-48 bg-white rounded-lg shadow-md overflow-hidden">
                    <a href="product_details.php?id=<?php echo $produitSimilaire['idProduct']; ?>" class="block">
                        <img src="<?php echo !empty($produitSimilaire['img']) ? htmlspecialchars($produitSimilaire['img']) : 'images/default.png'; ?>" 
                             alt="<?php echo !empty($produitSimilaire['name']) ? htmlspecialchars($produitSimilaire['name']) : 'Produit'; ?>" 
                             class="w-full h-36 object-cover">
                        <div class="p-4">
                            <h3 class="text-l hover:underline"><?php echo htmlspecialchars($produitSimilaire['name']); ?></h3>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Flèche droite -->
    <button class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-3 rounded-full hover:bg-gray-600 focus:outline-none z-10" aria-label="Suivant">
        <i class="fas fa-chevron-right"></i>
    </button>
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
<div id="overlay" class="hidden fixed inset-0 bg-gray-500 opacity-50 z-10"></div>

<script>

function fetchSuggestions(query) {
    const productId = <?php echo $idProduit; ?>; // Récupère l'ID du produit actuel
    if (query.length === 0) {
        document.getElementById("suggestions").innerHTML = "";
        document.getElementById("suggestions").classList.add("hidden");
        document.getElementById("suggestions").style.display = "none"; 
        document.getElementById("overlay").classList.add("hidden");
        return;
    }
    
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_suggestions.php?query=" + encodeURIComponent(query) + "&exclude=" + productId, true);
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


document.addEventListener('DOMContentLoaded', function() {
    const prevBtn = document.querySelector('[aria-label="Précédent"]');
    const nextBtn = document.querySelector('[aria-label="Suivant"]');
    const carouselContainer = document.querySelector('.overflow-hidden');
    const carousel = carouselContainer.querySelector('.flex');
    
    // Nombre d'éléments visibles à la fois
    const visibleItems = 6;
    const itemWidth = carousel.querySelector('div').offsetWidth + 24; // 24px pour l'espacement
    let currentPosition = 0;
    
    // Calculer le nombre total d'éléments et le nombre de groupes
    const totalItems = carousel.children.length;
    const totalGroups = Math.ceil((totalItems - visibleItems) / visibleItems);
    const maxPosition = (totalItems - visibleItems) * itemWidth;
    
    function updateButtons() {
        // Désactiver le bouton précédent si on est au début
        if (currentPosition <= 0) {
            prevBtn.style.opacity = '0.5';
            prevBtn.style.cursor = 'not-allowed';
            prevBtn.disabled = true;
        } else {
            prevBtn.style.opacity = '1';
            prevBtn.style.cursor = 'pointer';
            prevBtn.disabled = false;
        }
        
        // Désactiver le bouton suivant si on est à la fin
        if (currentPosition >= maxPosition) {
            nextBtn.style.opacity = '0.5';
            nextBtn.style.cursor = 'not-allowed';
            nextBtn.disabled = true;
        } else {
            nextBtn.style.opacity = '1';
            nextBtn.style.cursor = 'pointer';
            nextBtn.disabled = false;
        }
    }
    
    prevBtn.addEventListener('click', () => {
        if (currentPosition > 0) {
            // Défilement par groupe de 4
            currentPosition = Math.max(0, currentPosition - (visibleItems * itemWidth));
            carousel.style.transform = `translateX(-${currentPosition}px)`;
            updateButtons();
        }
    });
    
    nextBtn.addEventListener('click', () => {
        if (currentPosition < maxPosition) {
            // Défilement par groupe de 4
            const remainingItems = totalItems - (currentPosition / itemWidth + visibleItems);
            const moveItems = Math.min(visibleItems, remainingItems);
            currentPosition = Math.min(maxPosition, currentPosition + (moveItems * itemWidth));
            carousel.style.transform = `translateX(-${currentPosition}px)`;
            updateButtons();
        }
    });
    
    // État initial des boutons
    updateButtons();

    // Masquer les flèches s'il n'y a pas assez d'éléments
    if (totalItems <= visibleItems) {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
    }
});





</script>
</body>
</html>