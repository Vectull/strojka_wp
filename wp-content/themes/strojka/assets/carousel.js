document.querySelectorAll('[data-carousel]').forEach(carousel => {
  const track = carousel.querySelector('.carousel-track');
  const viewport = carousel.querySelector('.carousel-viewport');
  const controls = carousel.querySelector('.carousel-controls');
  const cards = Array.from(track.children);
  if (cards.length < 2) return;
  controls.hidden = false;
  carousel.classList.add('is-ready');
  let busy = false;
  let current = 0;
  const dots = carousel.querySelector('.carousel-dots');
  cards.forEach((card, index) => {
    const dot = document.createElement('button');
    dot.type = 'button';
    dot.setAttribute('aria-label', `Показать услугу ${index + 1}`);
    dot.addEventListener('click', () => {
      if (busy) return;
      while (track.firstElementChild !== card) track.append(track.firstElementChild);
      current = index;
      announce();
    });
    dots.append(dot);
  });
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const announce = () => {
    carousel.querySelector('.carousel-status').innerHTML = `<strong>${String(current + 1).padStart(2, '0')}</strong><span> / ${String(cards.length).padStart(2, '0')}</span>`;
    Array.from(dots.children).forEach((dot, index) => dot.setAttribute('aria-current', index === current ? 'true' : 'false'));
    // Only visible slides are keyboard reachable; no duplicate interactive cards.
    const width = viewport.clientWidth;
    const start = track.firstElementChild.offsetLeft;
    Array.from(track.children).forEach(card => {
      card.inert = card.offsetLeft - start + card.getBoundingClientRect().width > width + 1;
    });
  };
  const move = direction => {
    if (busy) return;
    busy = true;
    const step = track.firstElementChild.getBoundingClientRect().width + parseFloat(getComputedStyle(track).gap);
    const duration = reducedMotion.matches ? 0 : 280;
    track.style.transition = 'none';
    if (direction < 0) {
      track.prepend(track.lastElementChild);
      track.style.transform = `translateX(-${step}px)`;
    } else {
      // A noninteractive visual copy fills the right edge during the transition.
      const copy = track.firstElementChild.cloneNode(true);
      copy.inert = true;
      copy.setAttribute('aria-hidden', 'true');
      copy.dataset.copy = 'true';
      track.append(copy);
    }
    track.getBoundingClientRect();
    track.style.transition = `transform ${duration}ms ease`;
    track.style.transform = direction > 0 ? `translateX(-${step}px)` : 'translateX(0)';
    window.setTimeout(() => {
      track.style.transition = 'none';
      if (direction > 0) {
        track.querySelector('[data-copy]').remove();
        track.append(track.firstElementChild);
      }
      track.style.transform = '';
      current = (current + direction + cards.length) % cards.length;
      busy = false;
      announce();
    }, duration);
  };
  carousel.querySelector('.carousel-prev').addEventListener('click', () => move(-1));
  carousel.querySelector('.carousel-next').addEventListener('click', () => move(1));
  viewport.addEventListener('keydown', event => {
    if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
      event.preventDefault();
      move(event.key === 'ArrowRight' ? 1 : -1);
    }
  });
  let touchStart = null;
  viewport.addEventListener('touchstart', event => {
    touchStart = { x: event.touches[0].clientX, y: event.touches[0].clientY };
  }, { passive: true });
  viewport.addEventListener('touchend', event => {
    if (!touchStart) return;
    const dx = event.changedTouches[0].clientX - touchStart.x;
    const dy = event.changedTouches[0].clientY - touchStart.y;
    if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy)) move(dx < 0 ? 1 : -1);
    touchStart = null;
  }, { passive: true });
  viewport.addEventListener('touchcancel', () => { touchStart = null; }, { passive: true });
  new ResizeObserver(() => { if (!busy) announce(); }).observe(viewport);
  announce();
});
