/**
 * Shared Utility Functions
 */

// Helper to get user-specific localStorage key
function getUserKey(key) {
  const userId = window.CURRENT_USER_ID || 'guest';
  return `user_${userId}_${key}`;
}

// UI: Add horizontal scroll arrows to a container
function addScrollArrows(container) {
  // Check if arrows already exist in parent
  const parent = container.parentElement;
  if (!parent.classList.contains('section-container')) {
    // Wrap container if not already wrapped
    const wrapper = document.createElement('div');
    wrapper.className = 'section-container';
    parent.insertBefore(wrapper, container);
    wrapper.appendChild(container);
  }

  const wrapper = container.parentElement;
  if (wrapper.querySelector('.scroll-arrow')) return;

  const leftArrow = document.createElement('button');
  leftArrow.className = 'scroll-arrow arrow-left';
  leftArrow.innerHTML = '❮';
  leftArrow.onclick = () => container.scrollBy({ left: -400, behavior: 'smooth' });

  const rightArrow = document.createElement('button');
  rightArrow.className = 'scroll-arrow arrow-right';
  rightArrow.innerHTML = '❯';
  rightArrow.onclick = () => container.scrollBy({ left: 400, behavior: 'smooth' });

  wrapper.appendChild(leftArrow);
  wrapper.appendChild(rightArrow);

  // Hide/show arrows based on scroll position
  container.addEventListener('scroll', () => {
    leftArrow.style.display = container.scrollLeft <= 0 ? 'none' : 'flex';
    rightArrow.style.display = container.scrollLeft + container.clientWidth >= container.scrollWidth ? 'none' : 'flex';
  });
  
  // Initial check
  setTimeout(() => {
    leftArrow.style.display = container.scrollLeft <= 0 ? 'none' : 'flex';
    rightArrow.style.display = container.scrollWidth > container.clientWidth ? 'flex' : 'none';
  }, 500); 

  enableDragToScroll(container);
}

// UI: Enable drag-to-scroll functionality
function enableDragToScroll(slider) {
  let isDown = false;
  let startX;
  let scrollLeft;
  let isDragging = false;

  slider.addEventListener('mousedown', (e) => {
    isDown = true;
    isDragging = false;
    slider.classList.add('active');
    startX = e.clientX - slider.getBoundingClientRect().left;
    scrollLeft = slider.scrollLeft;
    slider.style.scrollBehavior = 'auto'; 
  });

  slider.addEventListener('mouseleave', () => {
    isDown = false;
    slider.classList.remove('active');
    slider.style.scrollBehavior = 'smooth';
  });

  slider.addEventListener('mouseup', (e) => {
    isDown = false;
    slider.classList.remove('active');
    slider.style.scrollBehavior = 'smooth';
    
    const x = e.clientX - slider.getBoundingClientRect().left;
    if (Math.abs(x - startX) > 5) {
      isDragging = true;
    }
  });

  slider.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.clientX - slider.getBoundingClientRect().left;
    const walk = (x - startX) * 2; 
    slider.scrollLeft = scrollLeft - walk;
    
    if (Math.abs(x - startX) > 5) {
      isDragging = true;
    }
  });

  // Prevent clicks if we were dragging
  slider.addEventListener('click', (e) => {
    if (isDragging) {
      e.stopImmediatePropagation();
      e.preventDefault();
      isDragging = false; 
    }
  }, true);
}
