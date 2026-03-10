// Cursor glow follows mouse
const glow = document.getElementById('glow');
const root = document.documentElement;

let mx = window.innerWidth / 2;
let my = window.innerHeight / 2;
let gx = mx, gy = my;

document.addEventListener('mousemove', e => {
  mx = e.clientX;
  my = e.clientY;
  root.style.setProperty('--cx', mx + 'px');
  root.style.setProperty('--cy', my + 'px');
});

// Smooth glow lag
function animateGlow() {
  gx += (mx - gx) * 0.08;
  gy += (my - gy) * 0.08;
  glow.style.left = gx + 'px';
  glow.style.top = gy + 'px';
  requestAnimationFrame(animateGlow);
}
animateGlow();

// Wrap name letters into spans
const nameEl = document.querySelector('.name');
nameEl.innerHTML = nameEl.innerHTML
  .split(/<br\s*\/?>/i)
  .map(line =>
    line.split('').map(ch => `<span class="char">${ch}</span>`).join('')
  )
  .join('<br>');

// Label words that cycle on idle
const words = [
  'Matyáš Prorok',
  'Matyáš Prorok',
  '—',
  'matyasprorok.cz',
  '—',
];

// Magnetic tilt on name hover
const hero = document.querySelector('.hero');
const content = document.querySelector('.content');

hero.addEventListener('mousemove', e => {
  const rect = content.getBoundingClientRect();
  const cx = rect.left + rect.width / 2;
  const cy = rect.top + rect.height / 2;
  const dx = (e.clientX - cx) / window.innerWidth;
  const dy = (e.clientY - cy) / window.innerHeight;
  content.style.transform = `translate(${dx * 12}px, ${dy * 8}px)`;
});

hero.addEventListener('mouseleave', () => {
  content.style.transform = '';
});

content.style.transition = 'transform 0.6s cubic-bezier(0.23, 1, 0.32, 1)';

// Rotating label — shows current time greeting
const label = document.getElementById('label');

function getGreeting() {
  const h = new Date().getHours();
  const m = new Date().getMinutes();

  if (h >= 5 && h < 12)  return 'Dobré ráno';
  if (h >= 12 && h < 18) return 'Dobré odpoledne';
  if (h >= 18 && h < 22) return 'Dobrý večer';
  return 'Dobrou noc';
}

function setLabel(text) {
  label.classList.remove('visible');
  setTimeout(() => {
    label.textContent = text;
    label.classList.add('visible');
  }, 300);
}

// Show greeting after short delay
setTimeout(() => {
  setLabel(getGreeting());
}, 600);

// Subtle parallax on scroll (if somehow triggered)
window.addEventListener('scroll', () => {
  const y = window.scrollY;
  nameEl.style.transform = `translateY(${y * 0.3}px)`;
});

// iDnes news ticker
async function loadTicker() {
  try {
    const res = await fetch('news.php');
    if (!res.ok) return;
    const data = await res.json();
    if (!data.items || !data.items.length) return;

    const content = document.getElementById('ticker-content');
    const bar     = document.getElementById('ticker-bar');

    content.innerHTML = data.items
      .map(item => `<a href="${item.link}" target="_blank" rel="noopener noreferrer">${item.title}</a>`)
      .join('&ensp;·&ensp;') + '&ensp;·&ensp;';

    // Délka animace podle počtu znaků — přibližně 80px/s
    const charCount = data.items.reduce((sum, i) => sum + i.title.length, 0);
    const duration  = Math.max(25, charCount * 0.09);
    content.style.animationDuration = duration + 's';

    bar.classList.add('loaded');
  } catch (e) {
    // Ticker zůstane skrytý při chybě
  }
}
loadTicker();

// Easter egg: Konami code
const konami = ['ArrowUp','ArrowUp','ArrowDown','ArrowDown','ArrowLeft','ArrowRight','ArrowLeft','ArrowRight','b','a'];
let ki = 0;
document.addEventListener('keydown', e => {
  if (e.key === konami[ki]) {
    ki++;
    if (ki === konami.length) {
      setLabel('✦ Nalezen.');
      setTimeout(() => setLabel(getGreeting()), 3000);
      ki = 0;
    }
  } else {
    ki = 0;
  }
});
