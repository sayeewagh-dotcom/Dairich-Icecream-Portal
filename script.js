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


/* ── B2B ENQUIRY FORM: submission ───────────────────────────── */
const submitBtn = document.querySelector('.btn-sub');

submitBtn.addEventListener('click', async () => {
  // Collect field values
  const inputs      = document.querySelectorAll('.enq-card .fg input, .enq-card .fg textarea');
  const fullName    = inputs[0]?.value.trim() || '';
  const email       = inputs[1]?.value.trim() || '';
  const message     = inputs[2]?.value.trim() || '';

  // Collect selected flavour buttons — map text to product IDs.
  // The data-id attribute should match the product id from the database.
  // Once you load products dynamically, these will carry real IDs.
  const selectedIds = [];
  document.querySelectorAll('.flav-btn.on').forEach(btn => {
    const pid = btn.dataset.id;
    if (pid) selectedIds.push(pid);
  });

  // Basic client-side validation
  if (!fullName) {
    showToast('Please enter your full name.', 'error'); return;
  }
  if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    showToast('Please enter a valid business email.', 'error'); return;
  }

  // Build FormData
  const formData = new FormData();
  formData.append('company_name',   fullName);   // adjust when company field is added
  formData.append('contact_person', fullName);
  formData.append('email',          email);
  formData.append('message',        message);
  formData.append('product_ids',    JSON.stringify(selectedIds));

  // Loading state
  submitBtn.disabled    = true;
  submitBtn.textContent = 'Submitting…';

  try {
    const res  = await fetch('api/enquiry/submit.php', {
      method: 'POST',
      body:   formData,
    });
    const data = await res.json();

    if (data.success) {
      showToast('Thank you! We will be in touch soon.', 'success');
      resetForm();
    } else {
      showToast(data.message || 'Something went wrong.', 'error');
    }
  } catch (err) {
    showToast('Network error. Please try again.', 'error');
  } finally {
    submitBtn.disabled    = false;
    submitBtn.textContent = 'Submit Partnership Request';
  }
});

/* Reset form after successful submission */
function resetForm() {
  document.querySelectorAll('.enq-card .fg input, .enq-card .fg textarea')
    .forEach(el => el.value = '');
  document.querySelectorAll('.flav-btn.on')
    .forEach(btn => btn.classList.remove('on'));
}

/* Lightweight toast notification */
function showToast(msg, type = 'success') {
  const existing = document.getElementById('dairich-toast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.id = 'dairich-toast';
  toast.textContent = msg;
  Object.assign(toast.style, {
    position:     'fixed',
    bottom:       '32px',
    left:         '50%',
    transform:    'translateX(-50%)',
    background:   type === 'success' ? '#1C2460' : '#C8102E',
    color:        '#fff',
    padding:      '14px 28px',
    borderRadius: '50px',
    fontSize:     '14px',
    fontWeight:   '600',
    zIndex:       '9999',
    boxShadow:    '0 8px 30px rgba(0,0,0,0.18)',
    transition:   'opacity 0.4s',
  });

  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 400);
  }, 3500);
}