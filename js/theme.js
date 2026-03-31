// js/theme.js

document.addEventListener('DOMContentLoaded', () => {
  const toggleBtn = document.getElementById('themeToggle');
  const savedTheme = localStorage.getItem(getUserKey('theme')) || 'dark';
  const container = document.querySelector('.tvq-tracker-container');

  if (container) {
    container.classList.add(savedTheme + '-mode');
  } else {
    document.body.classList.add(savedTheme + '-mode');
  }

  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      const target = document.querySelector('.tvq-tracker-container') || document.body;
      const isDark = target.classList.contains('dark-mode');
      target.classList.remove(isDark ? 'dark-mode' : 'light-mode');
      target.classList.add(isDark ? 'light-mode' : 'dark-mode');
      localStorage.setItem(getUserKey('theme'), isDark ? 'light' : 'dark');
    });
  }
});
