(() => {
  const canvas = document.getElementById('tactile-field');
  if (!canvas) return;

  const context = canvas.getContext('2d');
  const state = { width: 0, height: 0, pointerX: -100, pointerY: -100, targetX: -100, targetY: -100 };
  const accentColor = getComputedStyle(document.documentElement).getPropertyValue('--global-theme-color').trim();
  const points = [];
  const spacing = 28;
  const influence = 125;

  const resize = () => {
    const ratio = Math.min(window.devicePixelRatio || 1, 2);
    const bounds = canvas.getBoundingClientRect();
    state.width = bounds.width;
    state.height = bounds.height;
    canvas.width = Math.round(bounds.width * ratio);
    canvas.height = Math.round(bounds.height * ratio);
    context.setTransform(ratio, 0, 0, ratio, 0, 0);
    points.length = 0;
    for (let y = spacing; y < state.height; y += spacing) {
      for (let x = spacing; x < state.width; x += spacing) points.push({ x, y });
    }
  };

  const draw = () => {
    state.pointerX += (state.targetX - state.pointerX) * 0.12;
    state.pointerY += (state.targetY - state.pointerY) * 0.12;
    context.clearRect(0, 0, state.width, state.height);

    points.forEach((point) => {
      const distance = Math.hypot(point.x - state.pointerX, point.y - state.pointerY);
      const strength = Math.max(0, 1 - distance / influence);
      const lift = strength * strength * 18;
      const radius = 1.2 + strength * 2.5;
      context.beginPath();
      context.arc(point.x, point.y - lift, radius, 0, Math.PI * 2);
      context.fillStyle = accentColor;
      context.globalAlpha = 0.14 + strength * 0.58;
      context.fill();
    });
    context.globalAlpha = 1;

    requestAnimationFrame(draw);
  };

  const updatePointer = (event) => {
    const bounds = canvas.getBoundingClientRect();
    state.targetX = event.clientX - bounds.left;
    state.targetY = event.clientY - bounds.top;
  };

  canvas.addEventListener('pointermove', updatePointer);
  canvas.addEventListener('pointerleave', () => {
    state.targetX = state.width / 2;
    state.targetY = state.height / 2;
  });
  window.addEventListener('resize', resize);
  resize();
  state.targetX = state.width / 2;
  state.targetY = state.height / 2;
  draw();
})();
