// Theme Toggle
const themeToggle = document.getElementById('theme-toggle');
const root = document.documentElement;
const userTheme = localStorage.getItem('theme');
if (userTheme) root.setAttribute('data-theme', userTheme);
themeToggle.addEventListener('click', () => {
  const current = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  root.setAttribute('data-theme', current);
  localStorage.setItem('theme', current);
  themeToggle.textContent = current === 'dark' ? '☀️' : '🌙';
});
if (root.getAttribute('data-theme') === 'dark') themeToggle.textContent = '☀️';

// Smooth Scroll
const navLinks = document.querySelectorAll('.nav-link');
navLinks.forEach(link => {
  link.addEventListener('click', function(e) {
    if (this.hash) {
      e.preventDefault();
      document.querySelector(this.hash).scrollIntoView({ behavior: 'smooth' });
    }
  });
});
document.querySelector('.scroll-down').addEventListener('click', () => {
  document.getElementById('about').scrollIntoView({ behavior: 'smooth' });
});

// Navbar Active Highlight
const sections = document.querySelectorAll('section, header');
window.addEventListener('scroll', () => {
  let scrollPos = window.scrollY + 80;
  sections.forEach(sec => {
    if (scrollPos >= sec.offsetTop && scrollPos < sec.offsetTop + sec.offsetHeight) {
      navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href').slice(1) === sec.id) link.classList.add('active');
      });
    }
  });
});

// Animated Skills Progress Bars
function animateSkills() {
  document.querySelectorAll('.progress-bar').forEach(bar => {
    if (!bar.querySelector('.progress-bar-inner')) {
      const inner = document.createElement('div');
      inner.className = 'progress-bar-inner';
      bar.appendChild(inner);
    }
    const inner = bar.querySelector('.progress-bar-inner');
    const value = bar.getAttribute('data-skill');
    inner.style.width = value + '%';
  });
}
let skillsAnimated = false;
window.addEventListener('scroll', () => {
  const skillsSection = document.getElementById('about');
  if (!skillsAnimated && window.scrollY + window.innerHeight > skillsSection.offsetTop + 100) {
    animateSkills();
    skillsAnimated = true;
  }
});

// Modal for Project Details
const modal = document.getElementById('project-modal');
const modalBody = modal.querySelector('.modal-body');
document.querySelectorAll('.project-card').forEach(card => {
  card.addEventListener('click', () => {
    const title = card.querySelector('h3').textContent;
    const desc = card.querySelector('p').textContent;
    const img = card.querySelector('img').outerHTML;
    const links = card.querySelector('.project-links').outerHTML;
    modalBody.innerHTML = `<h3>${title}</h3>${img}<p>${desc}</p>${links}`;
    modal.style.display = 'flex';
  });
});
modal.querySelector('.close-modal').addEventListener('click', () => {
  modal.style.display = 'none';
});
window.addEventListener('click', e => {
  if (e.target === modal) modal.style.display = 'none';
});

// Contact Form Validation
const form = document.getElementById('contact-form');
const formMsg = document.getElementById('form-message');
form.addEventListener('submit', function(e) {
  e.preventDefault();
  const name = form.name.value.trim();
  const email = form.email.value.trim();
  const message = form.message.value.trim();
  if (!name || !email || !message) {
    formMsg.textContent = 'Please fill in all fields.';
    formMsg.style.color = 'red';
    return;
  }
  if (!/^\S+@\S+\.\S+$/.test(email)) {
    formMsg.textContent = 'Please enter a valid email address.';
    formMsg.style.color = 'red';
    return;
  }
  // For demo: show success, reset form
  formMsg.textContent = 'Message sent successfully!';
  formMsg.style.color = 'green';
  form.reset();
  // Uncomment below to use Formspree (replace YOUR_FORM_ID)
  // fetch('https://formspree.io/f/YOUR_FORM_ID', {
  //   method: 'POST',
  //   headers: { 'Accept': 'application/json' },
  //   body: new FormData(form)
  // }).then(r => {
  //   formMsg.textContent = 'Message sent!';
  //   formMsg.style.color = 'green';
  //   form.reset();
  // }).catch(() => {
  //   formMsg.textContent = 'Error sending message.';
  //   formMsg.style.color = 'red';
  // });
});

// Dynamic Year in Footer
document.getElementById('year').textContent = new Date().getFullYear();

// Fade-in on Scroll
function animateOnScroll() {
  document.querySelectorAll('[data-animate]').forEach(el => {
    const rect = el.getBoundingClientRect();
    if (rect.top < window.innerHeight - 60) {
      el.classList.add('animated');
    }
  });
}
window.addEventListener('scroll', animateOnScroll);
window.addEventListener('load', animateOnScroll);

// Hamburger menu for mobile
const navToggle = document.querySelector('.nav-toggle');
const navLinksList = document.querySelector('.nav-links');
navToggle.addEventListener('click', () => {
  navLinksList.classList.toggle('open');
});
navLinks.forEach(link => {
  link.addEventListener('click', () => {
    if (window.innerWidth <= 800) {
      navLinksList.classList.remove('open');
    }
  });
});

// Testimonials Slider
const testimonials = document.querySelectorAll('.testimonial');
const prevBtn = document.querySelector('.testimonial-prev');
const nextBtn = document.querySelector('.testimonial-next');
let testimonialIndex = 0;
function showTestimonial(idx) {
  testimonials.forEach((t, i) => t.classList.toggle('active', i === idx));
}
if (prevBtn && nextBtn) {
  prevBtn.addEventListener('click', () => {
    testimonialIndex = (testimonialIndex - 1 + testimonials.length) % testimonials.length;
    showTestimonial(testimonialIndex);
  });
  nextBtn.addEventListener('click', () => {
    testimonialIndex = (testimonialIndex + 1) % testimonials.length;
    showTestimonial(testimonialIndex);
  });
}
// Keyboard navigation for testimonials
if (prevBtn && nextBtn) {
  prevBtn.tabIndex = 0;
  nextBtn.tabIndex = 0;
  prevBtn.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') prevBtn.click(); });
  nextBtn.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') nextBtn.click(); });
}

// Animated Timeline
function animateTimeline() {
  document.querySelectorAll('.timeline-item[data-animate]').forEach(el => {
    const rect = el.getBoundingClientRect();
    if (rect.top < window.innerHeight - 60) {
      el.classList.add('animated');
    }
  });
}
window.addEventListener('scroll', animateTimeline);
window.addEventListener('load', animateTimeline);

// High Contrast Accessibility Toggle
const contrastToggle = document.getElementById('contrast-toggle');
contrastToggle.addEventListener('click', () => {
  document.body.classList.toggle('high-contrast');
  localStorage.setItem('contrast', document.body.classList.contains('high-contrast') ? 'on' : 'off');
});
// Load contrast mode from storage
if (localStorage.getItem('contrast') === 'on') {
  document.body.classList.add('high-contrast');
}

// Project Filtering
const filterBtns = document.querySelectorAll('.filter-btn');
const projectCards = document.querySelectorAll('.project-card');
filterBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    filterBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const filter = btn.getAttribute('data-filter');
    projectCards.forEach(card => {
      if (filter === 'all' || card.getAttribute('data-type') === filter) {
        card.style.display = '';
        setTimeout(() => card.classList.add('show'), 10);
      } else {
        card.classList.remove('show');
        setTimeout(() => card.style.display = 'none', 300);
      }
    });
  });
});

// Ensure all project cards are shown on load for animation
projectCards.forEach(card => card.classList.add('show'));

// Blog Section Functionality
const blogCards = document.querySelectorAll('.blog-card');
blogCards.forEach(card => {
  card.addEventListener('click', () => {
    // For demo purposes, show an alert
    const title = card.querySelector('h3').textContent;
    alert(`Opening article: "${title}"\n\nThis would typically open the full article or redirect to a blog post.`);
  });
  
  // Add keyboard accessibility
  card.setAttribute('tabindex', '0');
  card.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      card.click();
    }
  });
});

// Certifications Section Functionality
const certCards = document.querySelectorAll('.cert-card');
certCards.forEach(card => {
  card.addEventListener('click', () => {
    const title = card.querySelector('h3').textContent;
    const issuer = card.querySelector('.cert-issuer').textContent;
    alert(`Certificate: "${title}"\nIssued by: ${issuer}\n\nThis would typically open the verification page or show certificate details.`);
  });
  
  // Add keyboard accessibility
  card.setAttribute('tabindex', '0');
  card.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      card.click();
    }
  });
});

// Interactive Demo Section Functionality
const demoTabs = document.querySelectorAll('.demo-tab');
const demoPanels = document.querySelectorAll('.demo-panel');

// Tab switching functionality
demoTabs.forEach(tab => {
  tab.addEventListener('click', () => {
    const targetTab = tab.getAttribute('data-tab');
    
    // Remove active class from all tabs and panels
    demoTabs.forEach(t => t.classList.remove('active'));
    demoPanels.forEach(p => p.classList.remove('active'));
    
    // Add active class to clicked tab and corresponding panel
    tab.classList.add('active');
    document.getElementById(targetTab).classList.add('active');
  });
});

// Password Strength Checker
const passwordInput = document.getElementById('password-input');
const strengthFill = document.getElementById('strength-fill');
const strengthText = document.getElementById('strength-text');
const criteria = {
  length: document.getElementById('length-check'),
  uppercase: document.getElementById('uppercase-check'),
  lowercase: document.getElementById('lowercase-check'),
  number: document.getElementById('number-check'),
  special: document.getElementById('special-check')
};

passwordInput.addEventListener('input', () => {
  const password = passwordInput.value;
  let score = 0;
  let strength = 'Very Weak';
  let color = '#f44336';
  
  // Check criteria
  const hasLength = password.length >= 8;
  const hasUppercase = /[A-Z]/.test(password);
  const hasLowercase = /[a-z]/.test(password);
  const hasNumber = /\d/.test(password);
  const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
  
  // Update criteria display
  criteria.length.classList.toggle('valid', hasLength);
  criteria.uppercase.classList.toggle('valid', hasUppercase);
  criteria.lowercase.classList.toggle('valid', hasLowercase);
  criteria.number.classList.toggle('valid', hasNumber);
  criteria.special.classList.toggle('valid', hasSpecial);
  
  // Calculate score
  if (hasLength) score += 20;
  if (hasUppercase) score += 20;
  if (hasLowercase) score += 20;
  if (hasNumber) score += 20;
  if (hasSpecial) score += 20;
  
  // Determine strength
  if (score >= 80) {
    strength = 'Very Strong';
    color = '#4caf50';
  } else if (score >= 60) {
    strength = 'Strong';
    color = '#8bc34a';
  } else if (score >= 40) {
    strength = 'Medium';
    color = '#ff9800';
  } else if (score >= 20) {
    strength = 'Weak';
    color = '#ff5722';
  } else if (password.length > 0) {
    strength = 'Very Weak';
    color = '#f44336';
  } else {
    strength = 'Enter a password';
    color = '#9e9e9e';
  }
  
  // Update display
  strengthFill.style.width = score + '%';
  strengthFill.style.background = color;
  strengthText.textContent = strength;
  strengthText.style.color = color;
});

// Hash Generator
const hashInput = document.getElementById('hash-input');
const hashAlgorithm = document.getElementById('hash-algorithm');
const generateHashBtn = document.getElementById('generate-hash');
const hashOutput = document.getElementById('hash-output');
const copyHashBtn = document.getElementById('copy-hash');

generateHashBtn.addEventListener('click', async () => {
  const text = hashInput.value;
  const algorithm = hashAlgorithm.value;
  
  if (!text.trim()) {
    alert('Please enter some text to hash.');
    return;
  }
  
  try {
    const encoder = new TextEncoder();
    const data = encoder.encode(text);
    const hashBuffer = await crypto.subtle.digest(algorithm.toUpperCase(), data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    hashOutput.value = hashHex;
  } catch (error) {
    // Fallback for browsers that don't support Web Crypto API
    hashOutput.value = `Hash generation not supported in this browser. Algorithm: ${algorithm}`;
  }
});

copyHashBtn.addEventListener('click', () => {
  hashOutput.select();
  document.execCommand('copy');
  copyHashBtn.textContent = 'Copied!';
  setTimeout(() => {
    copyHashBtn.textContent = 'Copy Hash';
  }, 2000);
});

// Vulnerability Scanner Demo
const urlInput = document.getElementById('url-input');
const scanUrlBtn = document.getElementById('scan-url');
const scanResults = {
  https: document.getElementById('https-status'),
  headers: document.getElementById('headers-status'),
  ssl: document.getElementById('ssl-status'),
  overall: document.getElementById('overall-status')
};

scanUrlBtn.addEventListener('click', () => {
  const url = urlInput.value.trim();
  
  if (!url) {
    alert('Please enter a URL to scan.');
    return;
  }
  
  // Simulate scanning (in real implementation, this would make actual requests)
  scanResults.https.textContent = 'Checking...';
  scanResults.headers.textContent = 'Checking...';
  scanResults.ssl.textContent = 'Checking...';
  scanResults.overall.textContent = 'Checking...';
  
  setTimeout(() => {
    // Simulate results
    const isHttps = url.startsWith('https://');
    const hasGoodHeaders = Math.random() > 0.3;
    const hasValidSSL = isHttps && Math.random() > 0.2;
    
    scanResults.https.textContent = isHttps ? 'Good' : 'Bad';
    scanResults.https.className = `scan-status ${isHttps ? 'good' : 'bad'}`;
    
    scanResults.headers.textContent = hasGoodHeaders ? 'Good' : 'Warning';
    scanResults.headers.className = `scan-status ${hasGoodHeaders ? 'good' : 'warning'}`;
    
    scanResults.ssl.textContent = hasValidSSL ? 'Valid' : 'Invalid';
    scanResults.ssl.className = `scan-status ${hasValidSSL ? 'good' : 'bad'}`;
    
    const overallScore = (isHttps ? 1 : 0) + (hasGoodHeaders ? 1 : 0) + (hasValidSSL ? 1 : 0);
    if (overallScore >= 2) {
      scanResults.overall.textContent = 'Good';
      scanResults.overall.className = 'scan-status good';
    } else if (overallScore >= 1) {
      scanResults.overall.textContent = 'Warning';
      scanResults.overall.className = 'scan-status warning';
    } else {
      scanResults.overall.textContent = 'Poor';
      scanResults.overall.className = 'scan-status bad';
    }
  }, 2000);
});

// Push Notification Subscription (Firebase)
const subscribeBtn = document.getElementById('subscribe-push');
if (subscribeBtn && window.firebase && firebase.messaging) {
  const messaging = firebase.messaging();
  subscribeBtn.addEventListener('click', async () => {
    try {
      await Notification.requestPermission();
      const token = await messaging.getToken({ vapidKey: 'YOUR_PUBLIC_VAPID_KEY' });
      alert('Push subscription successful!\nYour token:\n' + token);
      // TODO: Send token to your server for real push
    } catch (err) {
      alert('Push subscription failed: ' + err.message);
    }
  });
} 