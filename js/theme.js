// js/theme.js

document.addEventListener('DOMContentLoaded', () => {
  const toggleBtn = document.getElementById('themeToggle');
  const savedTheme = localStorage.getItem(getUserKey('theme')) || 'dark';
  document.body.classList.add(savedTheme + '-mode');

  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      const isDark = document.body.classList.contains('dark-mode');
      document.body.classList.remove(isDark ? 'dark-mode' : 'light-mode');
      document.body.classList.add(isDark ? 'light-mode' : 'dark-mode');
      localStorage.setItem(getUserKey('theme'), isDark ? 'light' : 'dark');
    });
  }
});
// TV Tracker Theme Logic
