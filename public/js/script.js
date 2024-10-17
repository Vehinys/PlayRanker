// ------------------------------------------------------------------------------------------------ //
// ------------------------------------ SEARCH FORM SUBMISSION ------------------------------------ //
// ------------------------------------------------------------------------------------------------ //

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
// ------------------------------------------------------------------------------------------------ //

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
// ------------------------------------------------------------------------------------------------ //

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


// ------------------------------------------------------------------------------------------------ //
// ---------------------------------------- DROPDOWN ADMIN ---------------------------------------- //  
// ------------------------------------------------------------------------------------------------ //

    document.addEventListener("DOMContentLoaded", function() {
        const dropdowns = [
            { buttonId: "dropdown-types", menuId: "dropdown-types-menu" },
            { buttonId: "dropdown-categories", menuId: "dropdown-categories-menu" },
            { buttonId: "dropdown-platforms", menuId: "dropdown-platforms-menu" },
            { buttonId: "dropdown-rating", menuId: "dropdown-rating-menu" }
        ];

        // Fonction pour fermer tous les dropdowns sauf celui spécifié
        function closeAllDropdowns(exceptMenu = null) {
            dropdowns.forEach(d => {
                const menu = document.getElementById(d.menuId);
                if (menu !== exceptMenu) {
                    menu.classList.add("hidden");
                }
            });
        }

        // Boucle à travers chaque dropdown pour ajouter les événements de clic
        dropdowns.forEach(dropdown => {
            const dropdownButton = document.getElementById(dropdown.buttonId);
            const dropdownMenu = document.getElementById(dropdown.menuId);

            // Clic sur le bouton pour basculer le menu et fermer les autres
            dropdownButton.addEventListener("click", function(event) {
                // Empêche le clic de se propager à l'écouteur de clic global
                event.stopPropagation();
                closeAllDropdowns(dropdownMenu);
                dropdownMenu.classList.toggle("hidden");
            });
        });

        // Un seul événement pour fermer tous les dropdowns en cas de clic à l'extérieur
        document.addEventListener("click", function(event) {
            let clickedInsideDropdown = dropdowns.some(dropdown => {
                return event.target.closest(`#${dropdown.menuId}`) || event.target.closest(`#${dropdown.buttonId}`);
            });

            // Si le clic est à l'extérieur des menus et des boutons, tout fermer
            if (!clickedInsideDropdown) {
                closeAllDropdowns();
            }
        });
    });

// ------------------------------------------------------------------------------------------------ //
// ------------------------------------- DROPDOWN PAR CONSOLE ------------------------------------- // 
// ------------------------------------------------------------------------------------------------ // 
    
    function toggleDropdown(dropdownId) {
        // Close all dropdowns except the one being toggled
        document.querySelectorAll('[id^="dropdown-menu-"]').forEach(function(dropdown) {
            if (dropdown.id !== dropdownId) {
                dropdown.classList.add('hidden');
            }
        });

        // Toggle the selected dropdown
        const dropdown = document.getElementById(dropdownId);
        dropdown.classList.toggle('hidden');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.relative')) {
            document.querySelectorAll('[id^="dropdown-menu-"]').forEach(function(dropdown) {
                dropdown.classList.add('hidden');
            });
        }
    });


