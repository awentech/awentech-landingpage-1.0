/* ═══════════════════════════════════════════════════════════
   AWEN TECH — main.js
═══════════════════════════════════════════════════════════ */

/* ── Navbar scroll effect ─────────────────────────────────── */
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 40);
}, { passive: true });

/* ── Mobile menu ──────────────────────────────────────────── */
const burger   = document.getElementById('navBurger');
const navMobile = document.getElementById('navMobile');

burger.addEventListener('click', () => {
  navMobile.classList.toggle('open');
});

navMobile.querySelectorAll('a').forEach(link => {
  link.addEventListener('click', () => navMobile.classList.remove('open'));
});

/* ── Smooth scroll for all anchor links ───────────────────── */
document.querySelectorAll('a[href^="#"]').forEach(link => {
  link.addEventListener('click', e => {
    const target = document.querySelector(link.getAttribute('href'));
    if (!target) return;
    e.preventDefault();
    const offset = parseInt(getComputedStyle(document.documentElement)
      .getPropertyValue('--nav-h')) || 72;
    const top = target.getBoundingClientRect().top + window.scrollY - offset;
    window.scrollTo({ top, behavior: 'smooth' });
  });
});

/* ── Scroll-triggered fade animations ────────────────────────
   Attach .fade-up to elements you want animated            */
const fadeEls = document.querySelectorAll(
  '.service-card, .caso-card, .dif-card, .founder-card, .testi-card, ' +
  '.step, .proceso-highlight, .metric, .hero-float-card'
);
fadeEls.forEach((el, i) => {
  el.classList.add('fade-up');
  if (i % 4 === 1) el.classList.add('fade-up-delay-1');
  if (i % 4 === 2) el.classList.add('fade-up-delay-2');
  if (i % 4 === 3) el.classList.add('fade-up-delay-3');
});

const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.12 });

fadeEls.forEach(el => observer.observe(el));

/* ── Counter animation ────────────────────────────────────── */
function animateCounter(el, target, duration = 1600) {
  let start = 0;
  const step = target / (duration / 16);
  const timer = setInterval(() => {
    start = Math.min(start + step, target);
    el.textContent = Math.floor(start);
    if (start >= target) clearInterval(timer);
  }, 16);
}

const counterObserver = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.querySelectorAll('.metric-num').forEach(num => {
        animateCounter(num, parseInt(num.dataset.target, 10));
      });
      counterObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.3 });

const confianza = document.getElementById('confianza');
if (confianza) counterObserver.observe(confianza);

/* ── Contact Form ─────────────────────────────────────────── */
const form        = document.getElementById('contactForm');
const formSuccess = document.getElementById('formSuccess');
const submitBtn   = document.getElementById('submitBtn');
const btnText     = document.getElementById('btnText');
const btnLoader   = document.getElementById('btnLoader');

if (form) {
  form.addEventListener('submit', async e => {
    e.preventDefault();
    clearErrors();

    if (!validateForm()) return;

    // Check reCAPTCHA
    const recaptchaResponse = grecaptcha.getResponse();
    if (!recaptchaResponse) {
      showError('err-recaptcha', 'Por favor completá el reCAPTCHA.');
      return;
    }

    setLoading(true);

    const data = new FormData(form);
    data.append('g-recaptcha-response', recaptchaResponse);

    try {
      const res = await fetch('php/send_mail.php', {
        method: 'POST',
        body: data,
      });

      const json = await res.json();

      if (json.success) {
        form.style.display = 'none';
        formSuccess.style.display = 'block';
        formSuccess.scrollIntoView({ behavior: 'smooth', block: 'center' });
      } else {
        showError('err-mensaje', json.message || 'Hubo un error al enviar. Intentá de nuevo.');
        grecaptcha.reset();
      }
    } catch {
      showError('err-mensaje', 'Error de conexión. Por favor intentá de nuevo.');
      grecaptcha.reset();
    } finally {
      setLoading(false);
    }
  });
}

function validateForm() {
  let valid = true;

  const nombre = document.getElementById('nombre');
  if (!nombre.value.trim()) {
    showError('err-nombre', 'El nombre es requerido.');
    valid = false;
  }

  const email = document.getElementById('email');
  if (!email.value.trim()) {
    showError('err-email', 'El email es requerido.');
    valid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
    showError('err-email', 'Ingresá un email válido.');
    valid = false;
  }

  const necesidad = document.getElementById('necesidad');
  if (!necesidad.value) {
    showError('err-necesidad', 'Seleccioná una opción.');
    valid = false;
  }

  const mensaje = document.getElementById('mensaje');
  if (!mensaje.value.trim() || mensaje.value.trim().length < 20) {
    showError('err-mensaje', 'Contanos un poco más sobre tu proyecto (mínimo 20 caracteres).');
    valid = false;
  }

  return valid;
}

function showError(id, msg) {
  const el = document.getElementById(id);
  if (el) el.textContent = msg;
}

function clearErrors() {
  document.querySelectorAll('.field-error').forEach(el => { el.textContent = ''; });
}

function setLoading(loading) {
  submitBtn.disabled = loading;
  btnText.style.display  = loading ? 'none'         : 'inline';
  btnLoader.style.display = loading ? 'inline-flex'  : 'none';
}

/* ── Avatar fallback (show initials if no photo) ──────────── */
document.querySelectorAll('.founder-avatar img').forEach(img => {
  img.addEventListener('error', () => {
    img.style.display = 'none';
    img.parentElement.classList.add('avatar-initials');
  });
});
