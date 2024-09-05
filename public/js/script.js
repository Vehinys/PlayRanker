(() => {
    const bgParallax = document.querySelectorAll('.bg-parallax');
    const textParallax = document.querySelectorAll('.header-wild-world');
    const maxTranslateY = 128;

    document.addEventListener('scroll', () => {
        // Remplacer pageYOffset par scrollY
        let scrollPosition = window.scrollY;

        // Appliquer l'effet parallax aux éléments texte
        textParallax.forEach((e) => {
            let speed = e.getAttribute('data-speed');
            if (speed === null) return;

            speed = parseFloat(speed) || 1.00; 
            let translateY = (scrollPosition / 3) * speed;

            // Limiter la valeur de translation
            if (translateY > maxTranslateY) translateY = maxTranslateY;

            // Appliquer la translation verticale à l'élément texte
            e.style.transform = `translateX(-50%) translateY(${translateY}px)`;
        });

        // Appliquer l'effet parallax aux éléments de fond
        bgParallax.forEach((e) => {
            e.style.backgroundPositionY = `${scrollPosition * 0.5}px`;
        });
    });
})();
