// Animation de fond du hero : un réseau de nœuds façon "salle des machines"
// avec des paquets de données lumineux qui circulent sur les connexions
// actives — palette néon vert/cyan sur fond sombre, ambiance hacker/cyber.
// S'arrête sur une image fixe si l'utilisateur préfère moins d'animation.

const GREEN = [61, 220, 132];   // --green de la marque
const CYAN = [45, 212, 255];    // accent néon froid, contrepoint hacker
const LINK_DISTANCE = 140;
const NODE_DENSITY = 1 / 16000;
const MAX_NODES = 42;
const PULSE_SPAWN_CHANCE = 0.035; // par frame, probabilité qu'un nouveau paquet parte

export function mountHeroBackground(canvas) {
  const ctx = canvas.getContext('2d');
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  let width = 0;
  let height = 0;
  let dpr = Math.min(window.devicePixelRatio || 1, 2);
  let nodes = [];
  let pulses = [];
  let frameId = null;

  function resize() {
    const rect = canvas.parentElement.getBoundingClientRect();
    width = rect.width;
    height = rect.height;
    canvas.width = Math.round(width * dpr);
    canvas.height = Math.round(height * dpr);
    canvas.style.width = `${width}px`;
    canvas.style.height = `${height}px`;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    seedNodes();
    pulses = [];
  }

  function seedNodes() {
    const count = Math.min(MAX_NODES, Math.round(width * height * NODE_DENSITY));
    nodes = Array.from({ length: count }, () => ({
      x: Math.random() * width,
      y: Math.random() * height,
      vx: (Math.random() - 0.5) * 0.16,
      vy: (Math.random() - 0.5) * 0.16,
      r: Math.random() * 1.4 + 1.2,
      color: Math.random() < 0.65 ? GREEN : CYAN,
    }));
  }

  function moveNodes() {
    for (const n of nodes) {
      n.x += n.vx;
      n.y += n.vy;
      if (n.x < -20) n.x = width + 20;
      if (n.x > width + 20) n.x = -20;
      if (n.y < -20) n.y = height + 20;
      if (n.y > height + 20) n.y = -20;
    }
  }

  function findActiveEdges() {
    const edges = [];
    for (let i = 0; i < nodes.length; i++) {
      for (let j = i + 1; j < nodes.length; j++) {
        const a = nodes[i];
        const b = nodes[j];
        const dx = a.x - b.x;
        const dy = a.y - b.y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < LINK_DISTANCE) edges.push({ a, b, dist });
      }
    }
    return edges;
  }

  function drawEdges(edges) {
    ctx.lineWidth = 1;
    for (const e of edges) {
      const alpha = (1 - e.dist / LINK_DISTANCE) * 0.35;
      ctx.strokeStyle = `rgba(120, 200, 190, ${alpha})`;
      ctx.beginPath();
      ctx.moveTo(e.a.x, e.a.y);
      ctx.lineTo(e.b.x, e.b.y);
      ctx.stroke();
    }
  }

  function drawNodes() {
    for (const n of nodes) {
      const [r, g, b] = n.color;
      ctx.shadowColor = `rgba(${r}, ${g}, ${b}, 0.9)`;
      ctx.shadowBlur = 6;
      ctx.fillStyle = `rgba(${r}, ${g}, ${b}, 0.85)`;
      ctx.beginPath();
      ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
      ctx.fill();
    }
    ctx.shadowBlur = 0;
  }

  // --- Paquets de données : voyagent sur une connexion active, comme un flux réseau ---
  function maybeSpawnPulse(edges) {
    if (edges.length === 0) return;
    if (Math.random() > PULSE_SPAWN_CHANCE) return;
    const edge = edges[Math.floor(Math.random() * edges.length)];
    const forward = Math.random() < 0.5;
    pulses.push({
      from: forward ? edge.a : edge.b,
      to: forward ? edge.b : edge.a,
      t: 0,
      speed: 0.012 + Math.random() * 0.012,
      color: Math.random() < 0.5 ? GREEN : CYAN,
    });
  }

  function drawPulses() {
    ctx.globalCompositeOperation = 'lighter';
    pulses = pulses.filter((p) => p.t <= 1);
    for (const p of pulses) {
      p.t += p.speed;
      const x = p.from.x + (p.to.x - p.from.x) * p.t;
      const y = p.from.y + (p.to.y - p.from.y) * p.t;
      const [r, g, b] = p.color;

      // traînée courte derrière le paquet
      for (let k = 0; k < 4; k++) {
        const trailT = Math.max(0, p.t - k * 0.05);
        const tx = p.from.x + (p.to.x - p.from.x) * trailT;
        const ty = p.from.y + (p.to.y - p.from.y) * trailT;
        const alpha = (1 - k / 4) * 0.55;
        ctx.fillStyle = `rgba(${r}, ${g}, ${b}, ${alpha})`;
        ctx.beginPath();
        ctx.arc(tx, ty, k === 0 ? 2.4 : 1.4, 0, Math.PI * 2);
        ctx.fill();
      }

      ctx.shadowColor = `rgba(${r}, ${g}, ${b}, 1)`;
      ctx.shadowBlur = 10;
      ctx.fillStyle = 'rgba(255, 255, 255, 0.95)';
      ctx.beginPath();
      ctx.arc(x, y, 1.6, 0, Math.PI * 2);
      ctx.fill();
      ctx.shadowBlur = 0;
    }
    ctx.globalCompositeOperation = 'source-over';
  }

  function frame() {
    ctx.clearRect(0, 0, width, height);
    moveNodes();
    const edges = findActiveEdges();
    drawEdges(edges);
    drawNodes();
    maybeSpawnPulse(edges);
    drawPulses();
    frameId = requestAnimationFrame(frame);
  }

  resize();
  window.addEventListener('resize', resize);

  if (prefersReducedMotion) {
    // Une image fixe, sans boucle.
    frame();
    cancelAnimationFrame(frameId);
  } else {
    frame();
  }
}
