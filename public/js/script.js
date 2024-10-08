// ------------------------------------------------------------------------------------------------ //

// ------------------------------------ SEARCH FORM SUBMISSION ------------------------------------ //

    /**
     * Gère la fonctionnalité de soumission du formulaire de recherche.
     * Ce code met en place des écouteurs d'événements pour l'entrée de recherche et le bouton, 
     * et appelle la fonction `performSearch` lorsque l'utilisateur clique sur le bouton de 
     * recherche ou appuie sur la touche "Entrée" dans l'entrée de recherche.
     */

        // Gestion du formulaire de recherche
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput'); // Récupère l'input de recherche
            const searchButton = document.getElementById('searchButton'); // Récupère le bouton de recherche
            const resultsDiv = document.getElementById('results'); // Récupère la div pour les résultats

            // Ajoute un écouteur d'événement pour le clic sur le bouton de recherche
            searchButton.addEventListener('click', performSearch);

            // Ajoute un écouteur d'événement pour la touche "Enter" dans l'input de recherche
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
        });

// ------------------------------------------------------------------------------------------------ //

// --------------------------------------- DROPDOWNS GAMES ---------------------------------------- //    

    function toggleCategoriesDropdown() {
        const dropdown = document.getElementById('categories-dropdown');
        dropdown.classList.toggle('hidden');
    }

    // Renommez la deuxième fonction toggleDropdown pour éviter les conflits
    function toggleGameDropdown(dropdownId) {
        // Ferme tous les dropdowns avant d'ouvrir le bon
        document.querySelectorAll('.dropdown-content').forEach(function(dropdown) {
            dropdown.classList.add('hidden');
        });

        // Affiche ou cache le dropdown de la catégorie sélectionnée
        const dropdown = document.getElementById(dropdownId);
        dropdown.classList.toggle('hidden');
    }

// ------------------------------------------------------------------------------------------------ //

// ------------------------------------ BOUTON RETOUR EN HAUT ------------------------------------- //    

    /**
     * Ajoute un bouton "Retour en haut" à la page qui apparaît lorsque l'utilisateur défile au-delà de la section des avis.
     * Le bouton fait défiler la page en douceur vers le haut lorsqu'il est cliqué.
     */

        document.addEventListener('DOMContentLoaded', function () {
            var backToTopButton = document.createElement('a');
            backToTopButton.href = '#';
            backToTopButton.className = 'back-to-top';
            backToTopButton.innerHTML = '&#8593;'; // Flèche vers le haut
            document.body.appendChild(backToTopButton);

            window.addEventListener('scroll', function () {
                var reviewSection = document.querySelector('.review-section');
                if (reviewSection && window.pageYOffset > reviewSection.offsetTop) {
                    backToTopButton.classList.add('show');
                } else {
                    backToTopButton.classList.remove('show');
                }
            });

            backToTopButton.addEventListener('click', function (e) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });

// ------------------------------------------------------------------------------------------------ //

// ----------------------------------------- SLIDE REVIEW ----------------------------------------- //  

    document.addEventListener('DOMContentLoaded', () => {
        const reviewContainer = document.querySelector('.review-container');
        const reviews = document.querySelectorAll('.review-content');
        const nextButton = document.querySelector('.suivant');
        const prevButton = document.querySelector('.precedent');
        let currentIndex = 0;
        const totalReviews = reviews.length;

        function updateSlide() {
            const offset = -currentIndex * (100 / totalReviews);
            reviewContainer.style.transform = `translateX(${offset}%)`;
        }

        nextButton.addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % totalReviews;
            updateSlide();
        });

        prevButton.addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + totalReviews) % totalReviews;
            updateSlide();
        });

        // Initialisation
        updateSlide();
    });


