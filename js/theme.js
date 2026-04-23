// js/theme.js

/**
 * js/theme.js - Intelligent Theme Detection and Blocksy Integration
 */
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.tvq-tracker-container');
    const toggleBtn = document.getElementById('themeToggle');

    function applyTheme(theme) {
        if (!container) return;
        container.classList.remove('dark-mode', 'light-mode');
        container.classList.add(theme + '-mode');
    }

    // 1. Detect via Blocksy / Common Theme Patterns
    function detectTheme() {
        // Check for specific attributes used by Blocksy and other modern themes
        const html = document.documentElement;
        const body = document.body;

        if (html.hasAttribute('data-theme')) return html.getAttribute('data-theme');
        if (body.classList.contains('dark-mode') || html.classList.contains('dark-mode')) return 'dark';
        if (body.classList.contains('light-mode') || html.classList.contains('light-mode')) return 'light';

        // 2. Fallback to System Preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }

        // 3. Automatic Background Color Detection
        // We check the computed style of the body or a nearby wrapper
        const bgColor = window.getComputedStyle(body).backgroundColor;
        if (bgColor) {
            const rgb = bgColor.match(/\d+/g);
            if (rgb && rgb.length >= 3) {
                // simple brightness formula
                const brightness = (parseInt(rgb[0]) * 299 + parseInt(rgb[1]) * 587 + parseInt(rgb[2]) * 114) / 1000;
                return brightness < 128 ? 'dark' : 'light';
            }
        }

        return 'dark'; // Default
    }

    // Initial Apply
    const initialTheme = localStorage.getItem(getUserKey('theme')) || detectTheme();
    applyTheme(initialTheme);

    // Watch for changes (Blocksy/Theme Sync)
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' || mutation.type === 'childList') {
                applyTheme(detectTheme());
            }
        });
    });

    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme', 'class'] });
    observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });

    // Handle Manual Override
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const current = container.classList.contains('dark-mode') ? 'dark' : 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            localStorage.setItem(getUserKey('theme'), next);
        });
    }
});
