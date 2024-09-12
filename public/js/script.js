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

            flkty.slides.forEach(function (slide, i) {
                var image = slides[i]; 
                
                var x = (slide.target + flkty.x) * -1 / 3;
                image.style.backgroundPosition = x + 'px'; 
            });
        });
    }

    // Appelle la fonction pour initialiser les boutons avec effet de lueur
    generateGlowButtons();

    // Gestion de l'affichage du mot de passe
    const passwordField = document.getElementById('inputPassword');
    const togglePasswordIcon = document.getElementById('togglePassword');

    const confirmPasswordField = document.getElementById('inputConfirmPassword');
    const toggleConfirmPasswordIcon = document.getElementById('toggleConfirmPassword');

    function togglePasswordVisibility(field, icon) {
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.add('bx-show'); 
        } else {
            field.type = 'password';
            icon.classList.remove('bx-show');
        }
    }

    if (passwordField && togglePasswordIcon) {
        togglePasswordIcon.addEventListener('click', function() {
            togglePasswordVisibility(passwordField, togglePasswordIcon);
        });
    }

    if (confirmPasswordField && toggleConfirmPasswordIcon) {
        toggleConfirmPasswordIcon.addEventListener('click', function() {
            togglePasswordVisibility(confirmPasswordField, toggleConfirmPasswordIcon);
        });
    }
});

// Fonction pour générer les boutons avec effet de lueur
const generateGlowButtons = () => {
    document.querySelectorAll(".glow-button").forEach((button) => {
        let gradientElem = button.querySelector('.gradient');
        if (!gradientElem) {
            gradientElem = document.createElement("div");
            gradientElem.classList.add("gradient");
            button.appendChild(gradientElem);
        }

        button.removeEventListener("pointermove", handlePointerMove);
        button.addEventListener("pointermove", handlePointerMove);

        function handlePointerMove(e) {
            const rect = button.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            gsap.to(button, {
                "--pointer-x": `${x}px`,
                "--pointer-y": `${y}px`,
                duration: 0.6,
            });

            gsap.to(button, {
                "--button-glow": chroma
                .mix(
                    getComputedStyle(button)
                    .getPropertyValue("--button-glow-start")
                    .trim(),
                    getComputedStyle(button).getPropertyValue("--button-glow-end").trim(),
                    x / rect.width
                )
                .hex(),
                duration: 0.2,
            });
        }
    });
};

document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('searchthis');
        const searchBtn = document.getElementById('search-btn');
        
        searchBtn.addEventListener('click', function(event) {
          event.preventDefault();
          
          form.submit(); 
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const favoriteButtons = document.querySelectorAll('.favorite-button');

    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            this.classList.toggle('active');
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const incrementButtons = document.querySelectorAll('.increment-button');

    incrementButtons.forEach(button => {
        button.addEventListener('click', function() {
            const counterValueElement = this.querySelector('.counter-value');
            let currentValue = parseInt(counterValueElement.textContent, 10);
            counterValueElement.textContent = currentValue + 1;
        });
    });
});