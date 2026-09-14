// Animation de fond du hero : un réseau de nœuds qui dérivent lentement
// et se connectent quand ils sont proches, dans les couleurs de la marque.
// S'arrête (image fixe) si l'utilisateur préfère moins d'animations.

const NODE_COLOR = 'rgba(141, 149, 163, 0.55)';   // --muted
const LINK_COLOR = 'rgba(93, 101, 112, 0.35)';
const ACCENT_COLOR = 'rgba(61, 220, 132, 0.7)';    // --green
const ACCENT_LINK_COLOR = 'rgba(61, 220, 132, 0.22)';
const LINK_DISTANCE = 150;
const NODE_COUNT_DENSITY = 1 / 14000; // nœuds par pixel² de canvas
const MAX_NODES = 46;

export function mountHeroBackground(canvas) {
  const ctx = canvas.getContext('2d');
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let nodes = [];
  let width = 0;
  let height = 0;
  let dpr = Math.min(window.devicePixelRatio || 1, 2);
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
  }

  function seedNodes() {
    const count = Math.min(MAX_NODES, Math.round(width * height * NODE_COUNT_DENSITY));
    nodes = Array.from({ length: count }, (_, i) => ({
      x: Math.random() * width,
      y: Math.random() * height,
      vx: (Math.random() - 0.5) * 0.18,
      vy: (Math.random() - 0.5) * 0.18,
      r: Math.random() * 1.6 + 1,
      accent: i % 7 === 0, // une minorité de nœuds en vert accent, comme le diagramme
    }));
  }

  function step() {
    ctx.clearRect(0, 0, width, height);

    // déplacement
    for (const n of nodes) {
      n.x += n.vx;
      n.y += n.vy;
      if (n.x < -20) n.x = width + 20;
      if (n.x > width + 20) n.x = -20;
      if (n.y < -20) n.y = height + 20;
      if (n.y > height + 20) n.y = -20;
    }

    // connexions
    for (let i = 0; i < nodes.length; i++) {
      for (let j = i + 1; j < nodes.length; j++) {
        const a = nodes[i];
        const b = nodes[j];
        const dx = a.x - b.x;
        const dy = a.y - b.y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < LINK_DISTANCE) {
          const alphaFactor = 1 - dist / LINK_DISTANCE;
          ctx.strokeStyle = (a.accent && b.accent) ? ACCENT_LINK_COLOR : LINK_COLOR;
          ctx.globalAlpha = alphaFactor;
          ctx.lineWidth = 1;
          ctx.beginPath();
          ctx.moveTo(a.x, a.y);
          ctx.lineTo(b.x, b.y);
          ctx.stroke();
        }
      }
    }
    ctx.globalAlpha = 1;

    // nœuds
    for (const n of nodes) {
      ctx.fillStyle = n.accent ? ACCENT_COLOR : NODE_COLOR;
      ctx.beginPath();
      ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
      ctx.fill();
    }

    frameId = requestAnimationFrame(step);
  }

  resize();
  window.addEventListener('resize', resize);

  if (prefersReducedMotion) {
    // Une seule image statique, pas de boucle d'animation.
    step();
    cancelAnimationFrame(frameId);
  } else {
    step();
  }
}
