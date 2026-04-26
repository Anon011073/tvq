// js/theme.js

/**
 * js/theme.js - Global Theme Logic
 */
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.tvq-tracker-container');

    function applyTheme(theme) {
        if (!container) return;
        container.classList.remove('dark-mode', 'light-mode');
        container.classList.add(theme + '-mode');
    }

    // Strictly follow global admin setting
    const globalTheme = window.tvq_settings.global_theme || 'dark';
    applyTheme(globalTheme);
});
