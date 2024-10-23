document.addEventListener('DOMContentLoaded', () => {
    const urlSearchParams = new URLSearchParams(window.location.hash);
    const accessToken = urlSearchParams.get('access_token');

    if (accessToken) {
        // Utilisez des guillemets inversés pour l'interpolation de chaîne
        window.location.href = `/discord/check?access_token=${accessToken}`;
    }
});