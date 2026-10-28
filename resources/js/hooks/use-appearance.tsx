// La charte du T10 est conçue pour un fond clair : l'application reste en thème clair,
// même quand le système de l'utilisateur est en mode sombre.
export function initializeTheme() {
    document.documentElement.classList.remove('dark');
}
