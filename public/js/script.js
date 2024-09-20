// ------------------------------------------------------------------------------------------------ //

// ------------------------------------ SEARCH FORM SUBMISSION ------------------------------------ //

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

// ------------------------------------------------------------------------------------------------ //

// ------------------------------------------ DROPDOWNS ------------------------------------------- //    

    // Gestion des dropdowns
    const dropdowns = document.querySelectorAll('.dropdown'); // Récupère tous les éléments dropdown
    dropdowns.forEach(dropdown => {
        const dropdownContent = dropdown.querySelector('.dropdown-content'); // Récupère le contenu du dropdown

        // Affiche le dropdown au survol de la souris
        dropdown.addEventListener('mouseenter', () => {
            dropdownContent.classList.add('show');
        });

        // Masque le dropdown quand le curseur quitte
        dropdown.addEventListener('mouseleave', () => {
            dropdownContent.classList.remove('show');
        });
    });
});

// Fonction de recherche (à compléter selon ta logique)
function performSearch() {
    // Implémentation de la recherche ici
}

// ------------------------------------------------------------------------------------------------ //
