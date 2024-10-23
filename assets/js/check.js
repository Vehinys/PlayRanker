
    window.onload = function() {
        // Récupère le fragment d'URL après le #
        const hash = window.location.hash.substring(1);
        const params = new URLSearchParams(hash);
        const accessToken = params.get('access_token');
        
        if (accessToken) {
            // Redirige vers votre endpoint avec le token
            window.location.href = '/discord/check?access_token=' + accessToken;
        }
    }

