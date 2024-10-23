document.addEventListener('DOMContentLoaded', () => {
    // Supprime le '#' et crée un URLSearchParams à partir du hash
    const urlSearchParams = new URLSearchParams(window.location.hash.substring(1));
    const accessToken = urlSearchParams.get('access_token');

    console.log("Access Token récupéré :", accessToken); // Pour vérifier si le token est bien récupéré

    if (accessToken) {
        // Redirige vers la route '/discord/check' avec le token dans les paramètres d'URL
        window.location.href = `/discord/check?access_token=${accessToken}`;
    } else {
        console.error("Access token manquant dans le hash de l'URL");
    }
});
