/* ============================================
   DAIRICH ICE CREAM — MAIN JAVASCRIPT
   script.js
   ============================================ */


/* ── NAVBAR: shrink on scroll ── */
window.addEventListener('scroll', () => {
  document.getElementById('navbar').classList.toggle('shrunk', window.scrollY > 80);
});


/* ── SCROLL REVEAL with IntersectionObserver ── */
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('vis');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));


/* ── PRODUCTS: horizontal slider ── */
const track = document.getElementById('prodTrack');
let currentIndex = 0;

function getCardWidth() {
  return track.querySelector('.prod-card').offsetWidth + 24;
}

function getMaxIndex() {
  const totalCards = track.querySelectorAll('.prod-card').length;
  const visibleCards = Math.floor(track.parentElement.offsetWidth / getCardWidth());
  return totalCards - visibleCards;
}

document.getElementById('nextBtn').addEventListener('click', () => {
  if (currentIndex < getMaxIndex()) {
    currentIndex++;
    track.style.transform = `translateX(-${currentIndex * getCardWidth()}px)`;
  }
});

document.getElementById('prevBtn').addEventListener('click', () => {
  if (currentIndex > 0) {
    currentIndex--;
    track.style.transform = `translateX(-${currentIndex * getCardWidth()}px)`;
  }
});


/* ── ENQUIRY FORM: flavour pill toggle ── */
document.querySelectorAll('.flav-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    btn.classList.toggle('on');
  });
});


/* ── SMOOTH SCROLL: active nav link highlight ── */
const sections = document.querySelectorAll('section[id]');
const navLinks = document.querySelectorAll('.nav-center a');

const sectionObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      navLinks.forEach(link => link.classList.remove('active'));
      const activeLink = document.querySelector(`.nav-center a[href="#${entry.target.id}"]`);
      if (activeLink) activeLink.classList.add('active');
    }
  });
}, { threshold: 0.4 });

sections.forEach(section => sectionObserver.observe(section));
