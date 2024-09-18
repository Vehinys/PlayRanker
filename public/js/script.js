// ---------------------------------------------------------------------------------------------------- //

// DOMContentLoaded s'assure que le code est exécuté après que tout le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {

    // *** FLICKITY CAROUSEL CONFIGURATION *** : [ CODE DISPONIBLE : CODEPEN.IO ]
    // Configuration des options pour Flickity (un plugin de carousel)
    var options = {
        accessibility: true, // Permet l'accès au carousel via le clavier
        prevNextButtons: true, // Affiche les boutons pour naviguer entre les slides
        pageDots: true, // Affiche les points de pagination sous le carousel
        setGallerySize: false, // Ne définit pas la taille de la galerie automatiquement
        arrowShape: { // Personnalisation de la forme des flèches de navigation
            x0: 10, // Position du point de départ de la flèche
            x1: 60, // Position du premier point de la flèche
            y1: 50, // Position verticale du premier point
            x2: 60, // Position du deuxième point de la flèche
            y2: 45, // Position verticale du deuxième point
            x3: 15 // Position du dernier point de la flèche
        }
    };

    // Sélectionne l'élément du carousel dans le DOM
    var carousel = document.querySelector('[data-carousel]');
    if (carousel) {
        // Sélectionne toutes les cellules du carousel
        var slides = document.getElementsByClassName('carousel-cell');
        // Initialise Flickity avec les options définies
        var flkty = new Flickity(carousel, options);

        // Écoute l'événement de défilement pour ajuster la position de fond des images
        flkty.on('scroll', function () {
            flkty.slides.forEach(function (slide, i) {
                var image = slides[i]; 
                var x = (slide.target + flkty.x) * -1 / 3; // Calcule la nouvelle position de l'image
                image.style.backgroundPosition = x + 'px'; // Applique le déplacement du fond
            });
        });
    }

    // *** GLOW BUTTONS GENERATION ***
    // Appelle la fonction pour initialiser les boutons avec effet de lueur
    generateGlowButtons();

    // *** PASSWORD TOGGLE VISIBILITY ***
    // Gestion de l'affichage du mot de passe
    const passwordField = document.getElementById('inputPassword');
    const togglePasswordIcon = document.getElementById('togglePassword');

    const confirmPasswordField = document.getElementById('inputConfirmPassword');
    const toggleConfirmPasswordIcon = document.getElementById('toggleConfirmPassword');

    // Fonction pour afficher/masquer le mot de passe
    function togglePasswordVisibility(field, icon) {
        if (field.type === 'password') {
            field.type = 'text'; // Montre le mot de passe
            icon.classList.add('bx-show'); // Change l'icône
        } else {
            field.type = 'password'; // Cache le mot de passe
            icon.classList.remove('bx-show'); // Remet l'icône d'origine
        }
    }

    // Gestion des événements pour afficher/masquer le mot de passe
    if (passwordField && togglePasswordIcon) {
        togglePasswordIcon.addEventListener('click', function() {
            togglePasswordVisibility(passwordField, togglePasswordIcon);
        });
    }

    // Gestion pour le champ de confirmation de mot de passe
    if (confirmPasswordField && toggleConfirmPasswordIcon) {
        toggleConfirmPasswordIcon.addEventListener('click', function() {
            togglePasswordVisibility(confirmPasswordField, toggleConfirmPasswordIcon);
        });
    }
});


// ---------------------------------------------------------------------------------------------------- //

// *** GLOW BUTTONS LOGIC *** [ CODE DISPONIBLE : CODEPEN.IO ]
// Fonction pour générer les boutons avec effet de lueur
const generateGlowButtons = () => {
    document.querySelectorAll(".glow-button").forEach((button) => {
        let gradientElem = button.querySelector('.gradient');
        if (!gradientElem) {
            gradientElem = document.createElement("div");
            gradientElem.classList.add("gradient"); // Crée l'élément de gradient si absent
            button.appendChild(gradientElem); // L'ajoute au bouton
        }

        // Supprime tout écouteur précédent pour éviter la duplication
        button.removeEventListener("pointermove", handlePointerMove);
        // Ajoute un nouvel écouteur pour suivre les mouvements de la souris
        button.addEventListener("pointermove", handlePointerMove);

        // Fonction pour gérer le déplacement de la souris sur le bouton
        function handlePointerMove(e) {
            const rect = button.getBoundingClientRect(); // Obtient les dimensions du bouton
            const x = e.clientX - rect.left; // Coordonnée X de la souris relative au bouton
            const y = e.clientY - rect.top; // Coordonnée Y de la souris relative au bouton

            // Animation GSAP pour déplacer le gradient en fonction de la position de la souris
            gsap.to(button, {
                "--pointer-x": `${x}px`,
                "--pointer-y": `${y}px`,
                duration: 0.6, // Durée de l'animation
            });

            // Animation GSAP pour changer la couleur de lueur du bouton
            gsap.to(button, {
                "--button-glow": chroma
                .mix(
                    getComputedStyle(button)
                    .getPropertyValue("--button-glow-start")
                    .trim(),
                    getComputedStyle(button).getPropertyValue("--button-glow-end").trim(),
                    x / rect.width
                )
                .hex(), // Change la couleur selon la position
                duration: 0.2, // Durée du changement de couleur
            });
        }
    });
};


// ---------------------------------------------------------------------------------------------------- //

// *** SEARCH FORM SUBMISSION ***
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const resultsDiv = document.getElementById('results');

    searchButton.addEventListener('click', performSearch);
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    function performSearch() {
        const query = searchInput.value;
        const apiKey = 'c2caa004df8a4f65b23177fa9ca935f9';
        const apiUrl = `https://api.rawg.io/api/games?key=${apiKey}&search=${encodeURIComponent(query)}`;

        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                displayResults(data.results);
            })
            .catch(error => {
                console.error('Erreur lors de la recherche:', error);
            });
    }

    function displayResults(games) {
        resultsDiv.innerHTML = '';
        games.forEach(game => {
            const gameElement = document.createElement('div');
            gameElement.innerHTML = `
                <h3>${game.name}</h3>
                <img src="${game.background_image}" alt="${game.name}" style="width: 200px;">
                <p>Note : ${game.rating}/5</p>
            `;
            resultsDiv.appendChild(gameElement);
        });
    }
});

// ---------------------------------------------------------------------------------------------------- //



// ---------------------------------------------------------------------------------------------------- //

document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.dropdown');

    dropdowns.forEach(dropdown => {
        const dropdownContent = dropdown.querySelector('.dropdown-content');

        dropdown.addEventListener('mouseenter', function() {
            dropdownContent.classList.add('show'); // Affiche le dropdown au survol
        });

        dropdown.addEventListener('mouseleave', function() {
            dropdownContent.classList.remove('show'); // Masque le dropdown quand le curseur quitte
        });
    });
});





