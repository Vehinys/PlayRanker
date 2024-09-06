// Cette fonction est exécutée lorsque le contenu du DOM est entièrement chargé
document.addEventListener('DOMContentLoaded', function() {
    // Configuration des options pour Flickity (un plugin de carousel)
    var options = {
        accessibility: true, // Permet l'accès au carousel via le clavier
        prevNextButtons: true, // Affiche les boutons pour naviguer entre les slides
        pageDots: true, // Affiche les points de pagination sous le carousel
        setGallerySize: false, // Ne définit pas la taille de la galerie automatiquement
        arrowShape: {
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
            // Parcours chaque slide
            flkty.slides.forEach(function (slide, i) {
                var image = slides[i]; // Sélectionne l'image correspondante
                // Calcule la nouvelle position de fond pour créer un effet de parallaxe
                var x = (slide.target + flkty.x) * -1 / 3;
                image.style.backgroundPosition = x + 'px'; // Applique la nouvelle position de fond
            });
        });
    }

    // Appelle la fonction pour initialiser les boutons avec effet de lueur
    generateGlowButtons();
});

// Fonction pour générer les boutons avec effet de lueur
const generateGlowButtons = () => {
    // Sélectionne tous les boutons avec la classe .glow-button
    document.querySelectorAll(".glow-button").forEach((button) => {
        // Vérifie si l'élément gradient existe déjà
        let gradientElem = button.querySelector('.gradient');
        
        // Si l'élément gradient n'existe pas, crée-le
        if (!gradientElem) {
            gradientElem = document.createElement("div");
            gradientElem.classList.add("gradient");
            button.appendChild(gradientElem); // Ajoute l'élément gradient au bouton
        }

        // Nettoie les anciens événements pour éviter les doublons
        button.removeEventListener("pointermove", handlePointerMove);
        // Ajoute un nouvel événement pointermove pour gérer le déplacement du curseur
        button.addEventListener("pointermove", handlePointerMove);

        // Fonction pour gérer le mouvement du pointeur sur le bouton
        function handlePointerMove(e) {
            // Récupère la taille et la position du bouton
            const rect = button.getBoundingClientRect();
            // Calcule la position du pointeur par rapport au bouton
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            // Anime la position du pointeur avec GSAP
            gsap.to(button, {
                "--pointer-x": `${x}px`, // Met à jour la position horizontale du pointeur
                "--pointer-y": `${y}px`, // Met à jour la position verticale du pointeur
                duration: 0.6, // Durée de l'animation
            });

            // Anime la couleur de la lueur du bouton avec GSAP
            gsap.to(button, {
                "--button-glow": chroma
                .mix(
                    getComputedStyle(button) // Récupère les styles calculés du bouton
                    .getPropertyValue("--button-glow-start") // Récupère la couleur de départ
                    .trim(),
                    getComputedStyle(button).getPropertyValue("--button-glow-end").trim(), // Récupère la couleur de fin
                    x / rect.width // Mixe les couleurs en fonction de la position du pointeur
                )
                .hex(), // Convertit la couleur mixée en format hexadécimal
                duration: 0.2, // Durée de l'animation
            });
        }
    });
}
