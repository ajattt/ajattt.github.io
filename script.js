/**
 * SCRIPT.JS - DRAJAD WICAKSONO PORTOFOLIO
 * WebGL Lightning, Particle Text Animation, Starfield Parallax,
 * Navbar Mascot, Typewriter, Tab Filtering, Live Comments (localStorage),
 * SweetAlert2 Modals, and Interactive Confetti.
 */

document.addEventListener('DOMContentLoaded', () => {
  // Initialize Lucide Icons
  if (window.lucide) {
    window.lucide.createIcons();
  }

  // Initialize AOS Animation
  if (window.AOS) {
    window.AOS.init({
      duration: 800,
      once: false,
      offset: 50,
      easing: 'ease-out-cubic'
    });
  }

  // Update Year
  const yearEl = document.getElementById('current-year');
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  // Run modules
  initWelcomeScreen();
  initStarfieldParallax();
  initTypewriter();
  initNavScrollSpy();
  initCommentsSystem();
  initSpidermanSpotlight();
});

/* =========================================================================
   1. WELCOME SCREEN & WEBGL LIGHTNING + PARTICLE CANVAS
   ========================================================================= */
function initWelcomeScreen() {
  const welcomeScreen = document.getElementById('welcome-screen');
  const loaderPercent = document.getElementById('loader-percent');
  const loaderBar = document.getElementById('loader-bar');
  const loaderGlow = document.getElementById('loader-glow');
  const skipBtn = document.getElementById('skip-loader-btn');

  // 1. WebGL Lightning Canvas
  const canvasLightning = document.getElementById('lightning-canvas');
  if (canvasLightning) {
    setupLightningShader(canvasLightning);
  }

  // 2. Particle Text Canvas
  const canvasParticles = document.getElementById('particle-text-canvas');
  if (canvasParticles) {
    setupParticleText(canvasParticles);
  }

  // 3. Counter & Progress
  let progress = 0;
  const interval = setInterval(() => {
    progress += Math.floor(Math.random() * 3) + 1;
    if (progress >= 100) {
      progress = 100;
      clearInterval(interval);
      setTimeout(finishLoading, 400);
    }
    if (loaderPercent) loaderPercent.textContent = `${progress}%`;
    if (loaderBar) loaderBar.style.width = `${progress}%`;
    if (loaderGlow) loaderGlow.style.transform = `translateX(${progress - 100}%)`;
  }, 30);

  function finishLoading() {
    if (!welcomeScreen) return;
    welcomeScreen.style.opacity = '0';
    welcomeScreen.style.transform = 'scale(1.05)';
    welcomeScreen.style.pointerEvents = 'none';
    setTimeout(() => {
      welcomeScreen.style.display = 'none';
      if (window.AOS) window.AOS.refresh();
    }, 1000);
  }

  if (skipBtn) {
    skipBtn.addEventListener('click', () => {
      clearInterval(interval);
      if (loaderPercent) loaderPercent.textContent = '100%';
      if (loaderBar) loaderBar.style.width = '100%';
      finishLoading();
    });
  }
}

// WebGL Lightning Shader
function setupLightningShader(canvas) {
  const gl = canvas.getContext('webgl');
  if (!gl) return;

  const resize = () => {
    const dpr = Math.min(window.devicePixelRatio || 1, 1.5);
    canvas.width = canvas.clientWidth * dpr;
    canvas.height = canvas.clientHeight * dpr;
    gl.viewport(0, 0, canvas.width, canvas.height);
  };
  resize();
  window.addEventListener('resize', resize);

  const vertSrc = `
    attribute vec2 aPosition;
    void main() {
      gl_Position = vec4(aPosition, 0.0, 1.0);
    }
  `;

  const fragSrc = `
    precision mediump float;
    uniform vec2 iResolution;
    uniform float iTime;
    
    vec3 hsv2rgb(vec3 c) {
      vec3 rgb = clamp(abs(mod(c.x * 6.0 + vec3(0.0, 4.0, 2.0), 6.0) - 3.0) - 1.0, 0.0, 1.0);
      return c.z * mix(vec3(1.0), rgb, c.y);
    }
    float hash11(float p) {
      p = fract(p * .1031);
      p *= p + 33.33;
      p *= p + p;
      return fract(p);
    }
    float hash12(vec2 p) {
      vec3 p3 = fract(vec3(p.xyx) * .1031);
      p3 += dot(p3, p3.yzx + 33.33);
      return fract((p3.x + p3.y) * p3.z);
    }
    mat2 rotate2d(float theta) {
      float c = cos(theta);
      float s = sin(theta);
      return mat2(c, -s, s, c);
    }
    float noise(vec2 p) {
      vec2 ip = floor(p);
      vec2 fp = fract(p);
      float a = hash12(ip);
      float b = hash12(ip + vec2(1.0, 0.0));
      float c = hash12(ip + vec2(0.0, 1.0));
      float d = hash12(ip + vec2(1.0, 1.0));
      vec2 t = smoothstep(0.0, 1.0, fp);
      return mix(mix(a, b, t.x), mix(c, d, t.x), t.y);
    }
    float fbm(vec2 p) {
      float value = 0.0;
      float amplitude = 0.5;
      for (int i = 0; i < 6; ++i) {
        value += amplitude * noise(p);
        p *= rotate2d(0.45);
        p *= 2.0;
        amplitude *= 0.5;
      }
      return value;
    }
    void main() {
      vec2 uv = gl_FragCoord.xy / iResolution.xy;
      uv = 2.0 * uv - 1.0;
      float aspect = iResolution.x / iResolution.y;
      uv.x *= aspect;

      float path = sin(uv.y * 3.0 - iTime * 2.0) * 0.2 + sin(uv.y * 5.0 + iTime * 1.5) * 0.1;
      vec2 noiseUv = uv * 2.5;
      float n = 2.0 * fbm(noiseUv + 1.2 * iTime * 0.8) - 1.0;
      float center = path + n * 0.3;
      float dist = abs(uv.x - center);

      vec3 baseColor = hsv2rgb(vec3(220.0 / 360.0, 0.75, 0.9));
      vec3 col = baseColor * pow(mix(0.02, 0.08, hash11(iTime * 1.5)) / max(dist, 0.001), 1.1) * 1.3;
      float alpha = clamp(max(col.r, max(col.g, col.b)), 0.0, 0.85);
      gl_FragColor = vec4(col, alpha);
    }
  `;

  function createShader(type, src) {
    const shader = gl.createShader(type);
    gl.shaderSource(shader, src);
    gl.compileShader(shader);
    return shader;
  }

  const vShader = createShader(gl.VERTEX_SHADER, vertSrc);
  const fShader = createShader(gl.FRAGMENT_SHADER, fragSrc);
  const prog = gl.createProgram();
  gl.attachShader(prog, vShader);
  gl.attachShader(prog, fShader);
  gl.linkProgram(prog);
  gl.useProgram(prog);

  const posBuf = gl.createBuffer();
  gl.bindBuffer(gl.ARRAY_BUFFER, posBuf);
  gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, -1, 1, 1, -1, 1, 1]), gl.STATIC_DRAW);

  const aPos = gl.getAttribLocation(prog, "aPosition");
  gl.enableVertexAttribArray(aPos);
  gl.vertexAttribPointer(aPos, 2, gl.FLOAT, false, 0, 0);

  const uRes = gl.getUniformLocation(prog, "iResolution");
  const uTime = gl.getUniformLocation(prog, "iTime");

  const startTime = performance.now();
  function render(time) {
    gl.uniform2f(uRes, canvas.width, canvas.height);
    gl.uniform1f(uTime, (time - startTime) / 1000);
    gl.drawArrays(gl.TRIANGLES, 0, 6);
    requestAnimationFrame(render);
  }
  requestAnimationFrame(render);
}

// Particle Text Effect for Welcome Screen
function setupParticleText(canvas) {
  const ctx = canvas.getContext('2d');
  let particles = [];
  let width, height;

  function resize() {
    width = canvas.width = canvas.clientWidth || 400;
    height = canvas.height = canvas.clientHeight || 180;
    createParticles();
  }

  function createParticles() {
    const offCanvas = document.createElement('canvas');
    offCanvas.width = width;
    offCanvas.height = height;
    const offCtx = offCanvas.getContext('2d');

    offCtx.fillStyle = '#ffffff';
    offCtx.textAlign = 'center';
    offCtx.textBaseline = 'middle';

    const scale = Math.min(1, width / 500);
    offCtx.font = `bold ${Math.floor(28 * scale)}px Outfit, sans-serif`;
    offCtx.fillText("Welcome To My", width / 2, height / 2 - 20 * scale);

    offCtx.font = `bold ${Math.floor(36 * scale)}px Outfit, sans-serif`;
    offCtx.fillStyle = '#38bdf8';
    offCtx.fillText("Portofolio Website", width / 2, height / 2 + 25 * scale);

    const imgData = offCtx.getImageData(0, 0, width, height).data;
    particles = [];
    const step = 4;

    for (let y = 0; y < height; y += step) {
      for (let x = 0; x < width; x += step) {
        const index = (y * width + x) * 4;
        if (imgData[index + 3] > 128) {
          particles.push({
            x: Math.random() * width,
            y: Math.random() * height,
            originX: x,
            originY: y,
            vx: 0,
            vy: 0,
            color: `rgba(${imgData[index]}, ${imgData[index + 1]}, ${imgData[index + 2]}, 0.85)`,
            size: Math.random() * 2 + 1.2
          });
        }
      }
    }
  }

  resize();
  window.addEventListener('resize', resize);

  function animate() {
    ctx.clearRect(0, 0, width, height);

    for (let i = 0; i < particles.length; i++) {
      const p = particles[i];
      const dx = p.originX - p.x;
      const dy = p.originY - p.y;
      p.vx = (p.vx + dx * 0.05) * 0.85;
      p.vy = (p.vy + dy * 0.05) * 0.85;
      p.x += p.vx;
      p.y += p.vy;

      ctx.fillStyle = p.color;
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
      ctx.fill();
    }
    requestAnimationFrame(animate);
  }
  requestAnimationFrame(animate);
}

/* =========================================================================
   2. PARALLAX STARFIELD GENERATOR
   ========================================================================= */
function initStarfieldParallax() {
  const layer1 = document.getElementById('star-layer-1');
  const layer2 = document.getElementById('star-layer-2');
  const layer3 = document.getElementById('star-layer-3');

  function generateStarBoxShadow(count, color) {
    const shadows = [];
    for (let i = 0; i < count; i++) {
      const x = Math.floor(Math.random() * 3000) - 1500;
      const y = Math.floor(Math.random() * 3000);
      shadows.push(`${x}px ${y}px ${color}`);
    }
    return shadows.join(', ');
  }

  if (layer1) {
    layer1.style.boxShadow = generateStarBoxShadow(200, '#ffffff');
    layer1.style.width = '1px';
    layer1.style.height = '1px';
    layer1.style.borderRadius = '50%';
  }
  if (layer2) {
    layer2.style.boxShadow = generateStarBoxShadow(100, '#93c5fd');
    layer2.style.width = '2px';
    layer2.style.height = '2px';
    layer2.style.borderRadius = '50%';
  }
  if (layer3) {
    layer3.style.boxShadow = generateStarBoxShadow(40, '#38bdf8');
    layer3.style.width = '3px';
    layer3.style.height = '3px';
    layer3.style.borderRadius = '50%';
  }

  window.addEventListener('pointermove', (e) => {
    const cx = window.innerWidth / 2;
    const cy = window.innerHeight / 2;
    const dx = (e.clientX - cx) * 0.02;
    const dy = (e.clientY - cy) * 0.02;

    if (layer1) layer1.style.transform = `translate3d(${dx}px, ${dy}px, 0)`;
    if (layer2) layer2.style.transform = `translate3d(${dx * 1.5}px, ${dy * 1.5}px, 0)`;
    if (layer3) layer3.style.transform = `translate3d(${dx * 2.2}px, ${dy * 2.2}px, 0)`;
  }, { passive: true });
}

/* =========================================================================
   3. TYPEWRITER EFFECT
   ========================================================================= */
function initTypewriter() {
  const el = document.getElementById('typewriter-text');
  if (!el) return;

  const roles = [
    "Siswa Kelas XII TKJ",
    "Network Engineer Enthusiast",
    "Teknisi Jaringan & Komputer",
    "Pecinta Olahraga Badminton & Lari",
    "Junior Tech Explorer"
  ];

  let roleIdx = 0;
  let charIdx = 0;
  let isDeleting = false;

  function type() {
    const current = roles[roleIdx];
    if (isDeleting) {
      charIdx--;
      el.textContent = current.substring(0, charIdx);
    } else {
      charIdx++;
      el.textContent = current.substring(0, charIdx);
    }

    let delay = isDeleting ? 40 : 80;

    if (!isDeleting && charIdx === current.length) {
      delay = 2000;
      isDeleting = true;
    } else if (isDeleting && charIdx === 0) {
      isDeleting = false;
      roleIdx = (roleIdx + 1) % roles.length;
      delay = 500;
    }

    setTimeout(type, delay);
  }

  type();
}

/* =========================================================================
   4. FLOATING NAVBAR SCROLLSPY & MASCOT TRACKING
   ========================================================================= */
function initNavScrollSpy() {
  const navLinks = document.querySelectorAll('.nav-link');
  const mascot = document.getElementById('nav-mascot');
  const sections = ['Home', 'About', 'Portofolio', 'Contact'];

  function updateActiveNav() {
    const scrollPos = window.scrollY + 250;

    sections.forEach((id) => {
      const sec = document.getElementById(id);
      if (!sec) return;

      const top = sec.offsetTop;
      const height = sec.offsetHeight;

      if (scrollPos >= top && scrollPos < top + height) {
        navLinks.forEach((link) => {
          const glow = link.querySelector('.active-glow');
          if (link.dataset.nav === id) {
            link.classList.add('text-white');
            link.classList.remove('text-white/70');
            if (glow) glow.classList.remove('hidden');

            // Move Mascot to this button
            if (mascot) {
              const linkRect = link.getBoundingClientRect();
              const navRect = link.parentElement.getBoundingClientRect();
              const leftOffset = linkRect.left - navRect.left + (linkRect.width / 2) - 20;
              mascot.style.left = `${leftOffset}px`;
            }
          } else {
            link.classList.remove('text-white');
            link.classList.add('text-white/70');
            if (glow) glow.classList.add('hidden');
          }
        });
      }
    });
  }

  window.addEventListener('scroll', updateActiveNav, { passive: true });
  setTimeout(updateActiveNav, 300);
}

/* =========================================================================
   5. PORTFOLIO TABS SWITCHER
   ========================================================================= */
function switchTab(tabIndex) {
  for (let i = 0; i < 4; i++) {
    const pane = document.getElementById(`tab-content-${i}`);
    const btn = document.getElementById(`tab-btn-${i}`);

    if (i === tabIndex) {
      if (pane) {
        pane.classList.remove('hidden');
        pane.classList.add('block');
      }
      if (btn) {
        btn.classList.add('text-white', 'bg-blue-600/20', 'border-blue-500/30', 'shadow-lg');
        btn.classList.remove('text-slate-400');
      }
    } else {
      if (pane) {
        pane.classList.add('hidden');
        pane.classList.remove('block');
      }
      if (btn) {
        btn.classList.remove('text-white', 'bg-blue-600/20', 'border-blue-500/30', 'shadow-lg');
        btn.classList.add('text-slate-400');
      }
    }
  }

  if (window.AOS) {
    setTimeout(() => window.AOS.refresh(), 100);
  }
}
window.switchTab = switchTab;

/* =========================================================================
   6. COMMENTS SYSTEM (LOCALSTORAGE PERSISTENCE)
   ========================================================================= */
function initCommentsSystem() {
  renderComments();
}

function getStoredComments() {
  const data = localStorage.getItem('drajad_portfolio_comments');
  return data ? JSON.parse(data) : [];
}

function saveStoredComments(comments) {
  localStorage.setItem('drajad_portfolio_comments', JSON.stringify(comments));
}

function renderComments() {
  const container = document.getElementById('comments-list');
  const countEl = document.getElementById('comments-count');
  if (!container) return;

  const comments = getStoredComments();
  if (countEl) countEl.textContent = `(${comments.length + 1})`;

  // Keep Pinned Comment at top
  const pinnedHtml = `
    <div class="p-4 rounded-2xl bg-blue-500/10 border border-blue-500/30 space-y-2">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <img src="ajatnormal.jpeg" alt="Drajad Wicaksono" class="w-8 h-8 rounded-full object-cover border-2 border-blue-400" />
          <div>
            <div class="flex items-center gap-1.5">
              <span class="text-xs font-bold text-white">Drajad Wicaksono</span>
              <span class="px-1.5 py-0.5 text-[10px] rounded bg-blue-500 text-white font-mono font-bold">Admin</span>
            </div>
            <span class="text-[10px] text-blue-300 font-mono">Pinned Message</span>
          </div>
        </div>
        <span class="text-[10px] text-slate-400 font-mono">Official</span>
      </div>
      <p class="text-xs text-slate-200 leading-relaxed pl-10">
        Selamat datang di website portofolio resmi saya! Silakan jelajahi proyek TKJ saya atau tinggalkan pesan ramah kalian di kolom komentar ini. Salam hangat dari SMK Bangun Nusa Bangsa! 🚀
      </p>
    </div>
  `;

  let listHtml = pinnedHtml;

  comments.forEach((c) => {
    const timeAgo = formatTimeAgo(c.timestamp);
    const avatar = c.avatar || 'https://api.dicebear.com/7.x/bottts/svg?seed=' + encodeURIComponent(c.name);

    listHtml += `
      <div class="p-3.5 rounded-2xl bg-white/[0.03] border border-white/10 space-y-1.5 hover:border-white/20 transition-colors">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <img src="${avatar}" alt="${c.name}" class="w-7 h-7 rounded-full object-cover border border-blue-400/40 bg-slate-800" />
            <span class="text-xs font-bold text-slate-100">${escapeHtml(c.name)}</span>
          </div>
          <span class="text-[10px] text-slate-400 font-mono">${timeAgo}</span>
        </div>
        <p class="text-xs text-slate-300 leading-relaxed pl-9">
          ${escapeHtml(c.text)}
        </p>
      </div>
    `;
  });

  container.innerHTML = listHtml;
}

function handleCommentSubmit(e) {
  e.preventDefault();
  const nameInput = document.getElementById('comment-user-name');
  const textInput = document.getElementById('comment-content');
  const avatarInput = document.getElementById('comment-user-avatar');
  const submitBtn = document.getElementById('comment-submit-btn');

  if (!nameInput || !textInput) return;

  const name = nameInput.value.trim();
  const text = textInput.value.trim();

  if (!name || !text) return;

  submitBtn.disabled = true;
  submitBtn.innerHTML = `<span>Mengirim...</span>`;

  const newComment = {
    id: Date.now(),
    name: name,
    text: text,
    avatar: '',
    timestamp: Date.now()
  };

  const file = avatarInput && avatarInput.files ? avatarInput.files[0] : null;
  if (file) {
    const reader = new FileReader();
    reader.onload = function(evt) {
      newComment.avatar = evt.target.result;
      finishSaveComment(newComment, nameInput, textInput, avatarInput, submitBtn);
    };
    reader.readAsDataURL(file);
  } else {
    finishSaveComment(newComment, nameInput, textInput, avatarInput, submitBtn);
  }
}
window.handleCommentSubmit = handleCommentSubmit;

function finishSaveComment(newComment, nameInput, textInput, avatarInput, submitBtn) {
  const comments = getStoredComments();
  comments.unshift(newComment);
  saveStoredComments(comments);

  renderComments();

  nameInput.value = '';
  textInput.value = '';
  if (avatarInput) avatarInput.value = '';

  submitBtn.disabled = false;
  submitBtn.innerHTML = `<i data-lucide="send" class="w-3.5 h-3.5"></i><span>Kirim Komentar</span>`;
  if (window.lucide) window.lucide.createIcons();

  if (window.Swal) {
    window.Swal.fire({
      icon: 'success',
      title: 'Komentar Terkirim!',
      text: 'Terima kasih telah meninggalkan komentar di portofolio Drajad Wicaksono.',
      confirmButtonColor: '#2c67ed',
      timer: 2500,
      timerProgressBar: true,
      background: '#0f172a',
      color: '#ffffff'
    });
  }
}

function formatTimeAgo(timestamp) {
  const seconds = Math.floor((Date.now() - timestamp) / 1000);
  if (seconds < 60) return 'Baru saja';
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `${minutes}m lalu`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours}j lalu`;
  const days = Math.floor(hours / 24);
  return `${days}h lalu`;
}

function escapeHtml(str) {
  return str.replace(/[&<>"']/g, (m) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  })[m]);
}

/* =========================================================================
   7. CONTACT FORM SUBMISSION
   ========================================================================= */
function handleContactSubmit(e) {
  e.preventDefault();
  const name = document.getElementById('contact-name').value;
  const email = document.getElementById('contact-email').value;
  const message = document.getElementById('contact-message').value;
  const btn = document.getElementById('contact-submit-btn');

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = `<span class="animate-spin mr-2">&#9696;</span> Mengirim...`;
  }

  // Simulate smooth sending
  setTimeout(() => {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = `<i data-lucide="send" class="w-4 h-4"></i><span>Kirim Pesan</span>`;
      if (window.lucide) window.lucide.createIcons();
    }

    document.getElementById('contact-form').reset();

    if (window.Swal) {
      window.Swal.fire({
        icon: 'success',
        title: 'Pesan Berhasil Terkirim!',
        text: `Terima kasih ${name}, pesan Anda telah berhasil dikirim kepada Drajad Wicaksono.`,
        confirmButtonColor: '#2c67ed',
        background: '#0f172a',
        color: '#ffffff',
        timer: 3500,
        timerProgressBar: true
      });
    } else {
      alert(`Pesan berhasil terkirim! Terima kasih, ${name}.`);
    }

    triggerConfetti();
  }, 1000);
}
window.handleContactSubmit = handleContactSubmit;

/* =========================================================================
   8. MODALS & POPUPS
   ========================================================================= */
function openResumeModal() {
  const modal = document.getElementById('resume-modal');
  if (modal) {
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
}
window.openResumeModal = openResumeModal;

function closeResumeModal() {
  const modal = document.getElementById('resume-modal');
  if (modal) {
    modal.classList.add('hidden');
    document.body.style.overflow = '';
  }
}
window.closeResumeModal = closeResumeModal;

function openProjectModal(title, desc, tags) {
  const modal = document.getElementById('detail-modal');
  const titleEl = document.getElementById('modal-title');
  const descEl = document.getElementById('modal-desc');
  const tagsContainer = document.getElementById('modal-tags');

  if (titleEl) titleEl.textContent = title;
  if (descEl) descEl.textContent = desc;
  if (tagsContainer) {
    tagsContainer.innerHTML = tags.map(t => `
      <span class="px-2.5 py-1 text-xs rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30 font-mono">
        ${t}
      </span>
    `).join('');
  }

  if (modal) {
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
}
window.openProjectModal = openProjectModal;

function openCertModal(title, desc) {
  openProjectModal(title, desc, ['Sertifikat Resmi', 'Validasi SMK Bangun Nusa Bangsa', 'TKJ Certified']);
}
window.openCertModal = openCertModal;

function closeDetailModal() {
  const modal = document.getElementById('detail-modal');
  if (modal) {
    modal.classList.add('hidden');
    document.body.style.overflow = '';
  }
}
window.closeDetailModal = closeDetailModal;

// Close modals on Escape
window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeResumeModal();
    closeDetailModal();
  }
});

/* =========================================================================
   9. CONFETTI BURST
   ========================================================================= */
function triggerConfetti() {
  if (window.confetti) {
    window.confetti({
      particleCount: 70,
      spread: 60,
      origin: { y: 0.8 },
      colors: ['#2c67ed', '#38bdf8', '#60a5fa', '#ffffff', '#fbbf24']
    });
  }
}
window.triggerConfetti = triggerConfetti;

/* =========================================================================
   10. SPIDERMAN SPOTLIGHT & IDLE FADE ANIMATION
   ========================================================================= */
function initSpidermanSpotlight() {
  const container = document.getElementById('spiderman-profile-container');
  const cursorLayer = document.getElementById('cursor-spiderman-layer');
  const idleLayer = document.getElementById('idle-spiderman-layer');
  const cursorDot = document.getElementById('spiderman-cursor-dot');

  if (!container || !cursorLayer) return;

  let isHovered = false;
  let targetX = -1000;
  let targetY = -1000;
  let currentX = -1000;
  let currentY = -1000;

  function updateSpotlight() {
    if (isHovered) {
      currentX += (targetX - currentX) * 0.22;
      currentY += (targetY - currentY) * 0.22;

      const mask = `radial-gradient(circle 125px at ${currentX.toFixed(1)}px ${currentY.toFixed(1)}px, black 45%, transparent 100%)`;
      cursorLayer.style.webkitMaskImage = mask;
      cursorLayer.style.maskImage = mask;
      cursorLayer.style.opacity = '1';

      if (cursorDot) {
        cursorDot.style.left = `${currentX.toFixed(1)}px`;
        cursorDot.style.top = `${currentY.toFixed(1)}px`;
        cursorDot.style.opacity = '1';
      }
    } else {
      cursorLayer.style.opacity = '0';
      if (cursorDot) cursorDot.style.opacity = '0';
    }

    requestAnimationFrame(updateSpotlight);
  }
  requestAnimationFrame(updateSpotlight);

  function onPointerMove(e) {
    const rect = container.getBoundingClientRect();
    targetX = e.clientX - rect.left;
    targetY = e.clientY - rect.top;
  }

  function onPointerEnter(e) {
    isHovered = true;
    const rect = container.getBoundingClientRect();
    targetX = currentX = e.clientX - rect.left;
    targetY = currentY = e.clientY - rect.top;

    if (idleLayer) {
      idleLayer.style.opacity = '0';
      idleLayer.classList.remove('spiderman-idle-animate');
    }
  }

  function onPointerLeave() {
    isHovered = false;
    targetX = -1000;
    targetY = -1000;

    if (idleLayer) {
      idleLayer.style.opacity = '';
      idleLayer.classList.add('spiderman-idle-animate');
    }
  }

  container.addEventListener('mouseenter', onPointerEnter);
  container.addEventListener('mousemove', onPointerMove);
  container.addEventListener('mouseleave', onPointerLeave);

  // Touch Support
  container.addEventListener('touchstart', (e) => {
    if (e.touches && e.touches.length > 0) {
      onPointerEnter(e.touches[0]);
    }
  }, { passive: true });

  container.addEventListener('touchmove', (e) => {
    if (e.touches && e.touches.length > 0) {
      onPointerMove(e.touches[0]);
    }
  }, { passive: true });

  container.addEventListener('touchend', onPointerLeave);
}

