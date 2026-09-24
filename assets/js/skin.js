// Homepage hero: an interactive silicone "tactile skin" rendered with raw WebGL.
// The membrane dents under the cursor (or finger), ripples when pressed, and its
// embedded taxel grid lights up with a pressure heatmap, like a real tactile array.
// When nobody touches it, a ghost fingertip keeps exploring the surface.
(function () {
  "use strict";

  var canvas = document.getElementById("skin");
  if (!canvas) return;
  var gl = canvas.getContext("webgl", { antialias: true, alpha: true, premultipliedAlpha: true });
  if (!gl) {
    canvas.remove();
    return;
  }

  var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var small = window.innerWidth < 700;
  var N = small ? 120 : 190; // grid resolution (vertices per side - 1)
  var SIZE = [4.4, 3.1]; // world size of the silicone pad (x, z)

  // ---------------------------------------------------------------- shaders
  var VERT = [
    "precision highp float;",
    "attribute vec2 aUV;",
    "uniform mat4 uViewProj;",
    "uniform vec2 uSize;",
    "uniform float uTime;",
    "uniform float uBreath;",
    "uniform vec4 uTouch[4];", // x, z, depth 0..1, radius
    "uniform vec4 uRipple[6];", // x, z, start time, strength
    "varying vec3 vPos;",
    "varying vec3 vNormal;",
    "varying float vPress;",
    "varying vec2 vUV;",
    "float sq(float x) { return x * x; }",
    "float height(vec2 p, out float press) {",
    "  vec2 e2 = p / vec2(1.75, 1.2);",
    "  float h = 0.36 * exp(-dot(e2, e2));",
    "  h += uBreath * (sin(p.x * 1.6 + uTime * 0.55) * cos(p.y * 2.0 - uTime * 0.4) * 0.035",
    "            + sin((p.x + p.y) * 3.1 + uTime * 0.9) * 0.01);",
    "  press = 0.0;",
    "  for (int i = 0; i < 4; i++) {",
    "    vec4 t = uTouch[i];",
    "    if (t.z < 0.002) continue;",
    "    float r = length(p - t.xy);",
    "    float g = exp(-sq(r / t.w));",
    "    h -= t.z * 0.34 * g;",
    "    h += t.z * 0.06 * exp(-sq((r - t.w * 1.55) / (t.w * 0.75)));",
    "    press = max(press, t.z * g);",
    "  }",
    "  for (int i = 0; i < 6; i++) {",
    "    vec4 w = uRipple[i];",
    "    if (w.w < 0.002) continue;",
    "    float age = uTime - w.z;",
    "    if (age < 0.0 || age > 4.5) continue;",
    "    float r = length(p - w.xy);",
    "    float front = age * 1.35;",
    "    float env = exp(-age * 1.05) * exp(-sq((r - front) * 2.1));",
    "    h += w.w * 0.075 * sin((r - front) * 13.0) * env;",
    "    press = max(press, w.w * env * 0.55);",
    "  }",
    "  return h;",
    "}",
    "void main() {",
    "  vec2 p = (aUV - 0.5) * uSize;",
    "  float pr, d;",
    "  float h = height(p, pr);",
    "  float e = 0.012;",
    "  float hx = height(p + vec2(e, 0.0), d) - height(p - vec2(e, 0.0), d);",
    "  float hz = height(p + vec2(0.0, e), d) - height(p - vec2(0.0, e), d);",
    "  vNormal = normalize(vec3(-hx, 2.0 * e, -hz));",
    "  vPos = vec3(p.x, h, p.y);",
    "  vPress = pr;",
    "  vUV = aUV;",
    "  gl_Position = uViewProj * vec4(vPos, 1.0);",
    "}",
  ].join("\n");

  var FRAG = [
    "precision highp float;",
    "uniform vec3 uBase;",
    "uniform vec3 uViolet;",
    "uniform vec3 uCoral;",
    "uniform vec3 uCam;",
    "uniform vec2 uSize;",
    "uniform float uDark;",
    "varying vec3 vPos;",
    "varying vec3 vNormal;",
    "varying float vPress;",
    "varying vec2 vUV;",
    "void main() {",
    "  vec3 n = normalize(vNormal);",
    "  vec3 v = normalize(uCam - vPos);",
    "  vec3 L1 = normalize(vec3(-0.45, 1.0, 0.55));",
    "  vec3 L2 = normalize(vec3(0.9, 0.45, -0.25));",
    "  float wrap = 0.5;", // wrapped diffuse = soft, silicone-like light transport
    "  float d1 = max((dot(n, L1) + wrap) / (1.0 + wrap), 0.0);",
    "  float d2 = max((dot(n, L2) + wrap) / (1.0 + wrap), 0.0);",
    "  float spec = pow(max(dot(n, normalize(L1 + v)), 0.0), 60.0) * mix(0.16, 0.4, uDark);",
    "  float fres = pow(1.0 - max(dot(n, v), 0.0), 3.0);",
    "  float p = clamp(vPress, 0.0, 1.0);",
    "  vec3 heat = mix(uViolet, uCoral, smoothstep(0.35, 0.95, p));",
    "  vec3 col = uBase * (mix(0.36, 0.34, uDark) + 0.52 * d1 + 0.14 * d2);",
    "  col = mix(col, heat, smoothstep(0.03, 0.8, p) * 0.8);",
    "  col += uCoral * 0.10 * p;",
    // embedded taxel array
    "  vec2 cell = vUV * uSize * 8.0;",
    "  vec2 f = fract(cell) - 0.5;",
    "  float lit = smoothstep(0.02, 0.45, p);",
    "  float dotMask = smoothstep(0.12 + 0.06 * lit, 0.07 + 0.04 * lit, length(f));",
    "  vec3 taxel = mix(col * mix(0.8, 1.5, uDark), heat * 1.4, lit);",
    "  col = mix(col, taxel, dotMask * (0.55 + 0.45 * lit));",
    "  col += spec;",
    "  col += fres * mix(uViolet, vec3(1.0), 0.35) * mix(0.16, 0.5, uDark);",
    // fade the membrane edges into the page
    "  vec2 q = abs(vUV - 0.5) * 2.0;",
    "  float sq4 = pow(pow(q.x, 4.0) + pow(q.y, 4.0), 0.25);",
    "  float alpha = smoothstep(1.0, 0.86, sq4);",
    "  gl_FragColor = vec4(col * alpha, alpha);",
    "}",
  ].join("\n");

  function compile(type, src) {
    var s = gl.createShader(type);
    gl.shaderSource(s, src);
    gl.compileShader(s);
    if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) throw new Error(gl.getShaderInfoLog(s));
    return s;
  }
  var prog;
  try {
    prog = gl.createProgram();
    gl.attachShader(prog, compile(gl.VERTEX_SHADER, VERT));
    gl.attachShader(prog, compile(gl.FRAGMENT_SHADER, FRAG));
    gl.linkProgram(prog);
    if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) throw new Error(gl.getProgramInfoLog(prog));
  } catch (err) {
    console.warn("skin.js:", err);
    canvas.remove();
    return;
  }
  gl.useProgram(prog);
  document.documentElement.classList.add("has-skin");

  // ---------------------------------------------------------------- mesh
  var uv = new Float32Array((N + 1) * (N + 1) * 2);
  for (var j = 0, k = 0; j <= N; j++) {
    for (var i = 0; i <= N; i++) {
      uv[k++] = i / N;
      uv[k++] = j / N;
    }
  }
  var idx = new Uint16Array(N * N * 6);
  for (j = 0, k = 0; j < N; j++) {
    for (i = 0; i < N; i++) {
      var a = j * (N + 1) + i, b = a + 1, c = a + N + 1, d = c + 1;
      idx[k++] = a; idx[k++] = c; idx[k++] = b;
      idx[k++] = b; idx[k++] = c; idx[k++] = d;
    }
  }
  var vbo = gl.createBuffer();
  gl.bindBuffer(gl.ARRAY_BUFFER, vbo);
  gl.bufferData(gl.ARRAY_BUFFER, uv, gl.STATIC_DRAW);
  var ibo = gl.createBuffer();
  gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, ibo);
  gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, idx, gl.STATIC_DRAW);
  var aUV = gl.getAttribLocation(prog, "aUV");
  gl.enableVertexAttribArray(aUV);
  gl.vertexAttribPointer(aUV, 2, gl.FLOAT, false, 0, 0);

  var U = {};
  ["uViewProj", "uSize", "uTime", "uBreath", "uTouch", "uRipple", "uBase", "uViolet", "uCoral", "uCam", "uDark"].forEach(function (n) {
    U[n] = gl.getUniformLocation(prog, n);
  });
  gl.uniform2f(U.uSize, SIZE[0], SIZE[1]);
  gl.enable(gl.DEPTH_TEST);
  gl.enable(gl.BLEND);
  gl.blendFunc(gl.ONE, gl.ONE_MINUS_SRC_ALPHA);

  // ---------------------------------------------------------------- camera
  var eye, target, fov = 36, viewProj, camBasis;
  function sub(a, b) { return [a[0] - b[0], a[1] - b[1], a[2] - b[2]]; }
  function cross(a, b) { return [a[1] * b[2] - a[2] * b[1], a[2] * b[0] - a[0] * b[2], a[0] * b[1] - a[1] * b[0]]; }
  function norm(a) { var l = Math.hypot(a[0], a[1], a[2]); return [a[0] / l, a[1] / l, a[2] / l]; }
  function dot(a, b) { return a[0] * b[0] + a[1] * b[1] + a[2] * b[2]; }

  function setupCamera(aspect) {
    // Taller (phone) screens look more from above so the skin still fills the view.
    // On landscape screens the camera looks left of the pad, so the pad sits on the
    // right and leaves room for the headline.
    var shift = -1.65 * Math.max(0, Math.min(1, (aspect - 1) / 0.75));
    target = aspect < 1 ? [0, 0, 0.35] : [shift, 0.1, 0.15];
    eye = aspect < 1 ? [0, 4.6, 3.9] : [shift, 3.05, 4.45];
    var f = norm(sub(target, eye));
    var r = norm(cross(f, [0, 1, 0]));
    var u = cross(r, f);
    camBasis = { f: f, r: r, u: u, t: Math.tan((fov * Math.PI) / 360), aspect: aspect };
    var view = [
      r[0], u[0], -f[0], 0,
      r[1], u[1], -f[1], 0,
      r[2], u[2], -f[2], 0,
      -dot(r, eye), -dot(u, eye), dot(f, eye), 1,
    ];
    var near = 0.1, far = 30, t = 1 / camBasis.t;
    var proj = [t / aspect, 0, 0, 0, 0, t, 0, 0, 0, 0, (far + near) / (near - far), -1, 0, 0, (2 * far * near) / (near - far), 0];
    viewProj = new Float32Array(16);
    for (var c = 0; c < 4; c++)
      for (var rr = 0; rr < 4; rr++) {
        var s = 0;
        for (var m = 0; m < 4; m++) s += proj[m * 4 + rr] * view[c * 4 + m];
        viewProj[c * 4 + rr] = s;
      }
    gl.uniformMatrix4fv(U.uViewProj, false, viewProj);
    gl.uniform3f(U.uCam, eye[0], eye[1], eye[2]);
  }

  // Screen position -> point on the membrane plane (y = 0).
  function pick(clientX, clientY) {
    var rect = canvas.getBoundingClientRect();
    var nx = ((clientX - rect.left) / rect.width) * 2 - 1;
    var ny = 1 - ((clientY - rect.top) / rect.height) * 2;
    var B = camBasis;
    var dir = norm([
      B.f[0] + nx * B.t * B.aspect * B.r[0] + ny * B.t * B.u[0],
      B.f[1] + nx * B.t * B.aspect * B.r[1] + ny * B.t * B.u[1],
      B.f[2] + nx * B.t * B.aspect * B.r[2] + ny * B.t * B.u[2],
    ]);
    if (dir[1] >= -0.001) return null;
    var t = (0.22 - eye[1]) / dir[1]; // plane near the top of the domed pad
    return [eye[0] + dir[0] * t, eye[2] + dir[2] * t];
  }

  // ---------------------------------------------------------------- colours
  function hexToRgb(h) {
    h = (h || "").trim();
    var m = /^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i.exec(h);
    return m ? [parseInt(m[1], 16) / 255, parseInt(m[2], 16) / 255, parseInt(m[3], 16) / 255] : [0.9, 0.86, 0.84];
  }
  function readColors() {
    var cs = getComputedStyle(document.documentElement);
    var base = hexToRgb(cs.getPropertyValue("--skin-surface"));
    var v = hexToRgb(cs.getPropertyValue("--violet"));
    var c = hexToRgb(cs.getPropertyValue("--coral"));
    gl.uniform3f(U.uBase, base[0], base[1], base[2]);
    gl.uniform3f(U.uViolet, v[0], v[1], v[2]);
    gl.uniform3f(U.uCoral, c[0], c[1], c[2]);
    gl.uniform1f(U.uDark, document.documentElement.getAttribute("data-theme") === "dark" ? 1 : 0);
    wake();
  }
  new MutationObserver(readColors).observe(document.documentElement, { attributes: true, attributeFilter: ["data-theme"] });

  // ---------------------------------------------------------------- state
  var touches = [0, 1, 2, 3].map(function () { return { x: 0, z: 0, depth: 0, target: 0, radius: 0.42 }; });
  var finger = touches[0], ghost = touches[1];
  var ripples = [];
  var touchData = new Float32Array(16);
  var rippleData = new Float32Array(24);
  var start = performance.now();
  var now = 0, lastInput = -10, pointerInside = false, pressed = false, nextGhostTap = 3;
  var readout = document.querySelector("[data-skin-readout]");
  var cursor = document.querySelector(".touch-cursor");

  function addRipple(x, z, strength) {
    ripples.push({ x: x, z: z, t: now, s: strength });
    if (ripples.length > 6) ripples.shift();
  }

  function onMove(ev) {
    var p = pick(ev.clientX, ev.clientY);
    lastInput = now;
    if (cursor) {
      cursor.style.transform = "translate(" + ev.clientX + "px," + ev.clientY + "px)";
      cursor.classList.add("is-on");
    }
    if (!p) return;
    finger.x = p[0];
    finger.z = p[1];
    pointerInside = true;
    finger.target = pressed ? 1 : 0.45;
    wake();
  }
  canvas.addEventListener("pointermove", onMove, { passive: true });
  canvas.addEventListener("pointerdown", function (ev) {
    pressed = true;
    onMove(ev);
    finger.target = 1;
    addRipple(finger.x, finger.z, 1);
    if (cursor) cursor.classList.add("is-pressed");
  });
  window.addEventListener("pointerup", function () {
    if (!pressed) return;
    pressed = false;
    finger.target = pointerInside ? 0.45 : 0;
    if (cursor) cursor.classList.remove("is-pressed");
    wake();
  });
  canvas.addEventListener("pointerleave", function () {
    pointerInside = false;
    finger.target = 0;
    if (cursor) cursor.classList.remove("is-on", "is-pressed");
    wake();
  });

  // ---------------------------------------------------------------- loop
  var running = false, visible = true, raf = 0, lastFrame = 0;
  function wake() {
    if (!running && visible && !document.hidden) {
      running = true;
      raf = requestAnimationFrame(frame);
    }
  }

  function frame(ts) {
    raf = 0;
    now = (ts - start) / 1000;
    var dt = Math.min(0.05, now - lastFrame);
    lastFrame = now;

    // Ghost fingertip explores the skin when nobody is touching it.
    var idle = !reduce && now - lastInput > 2.2;
    if (idle) {
      var g = now * 0.9;
      ghost.x = Math.sin(g * 0.41) * 1.15 + Math.sin(g * 1.1) * 0.2;
      ghost.z = Math.sin(g * 0.63 + 1.2) * 0.7;
      ghost.target = 0.42 + 0.22 * Math.sin(g * 1.7);
      if (now > nextGhostTap) {
        addRipple(ghost.x, ghost.z, 0.7);
        ghost.depth = 1;
        nextGhostTap = now + 2.6 + Math.random() * 2.2;
      }
    } else {
      ghost.target = 0;
    }

    var moving = false;
    touches.forEach(function (t, i) {
      var k = t.target > t.depth ? 10 : 4; // press quickly, release softly
      t.depth += (t.target - t.depth) * Math.min(1, dt * k);
      if (Math.abs(t.target - t.depth) > 0.002) moving = true;
      touchData[i * 4] = t.x;
      touchData[i * 4 + 1] = t.z;
      touchData[i * 4 + 2] = t.depth;
      touchData[i * 4 + 3] = t.radius;
    });
    rippleData.fill(0);
    ripples.forEach(function (r, i) {
      if (now - r.t < 4.5) moving = true;
      rippleData[i * 4] = r.x;
      rippleData[i * 4 + 1] = r.z;
      rippleData[i * 4 + 2] = r.t;
      rippleData[i * 4 + 3] = r.s;
    });

    gl.uniform1f(U.uTime, now);
    gl.uniform1f(U.uBreath, reduce ? 0 : 1);
    gl.uniform4fv(U.uTouch, touchData);
    gl.uniform4fv(U.uRipple, rippleData);
    gl.clearColor(0, 0, 0, 0);
    gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
    gl.drawElements(gl.TRIANGLES, idx.length, gl.UNSIGNED_SHORT, 0);

    if (readout) {
      var pmax = Math.max(finger.depth, ghost.depth);
      readout.innerHTML =
        "<span>pressure <b>" + (pmax * 38).toFixed(1) + " kPa</b></span>" +
        "<span>active taxels <b>" + Math.round(pmax * 46) + "</b></span>" +
        "<span>contact <b>" + (pmax > 0.05 ? (finger.depth >= ghost.depth ? "you" : "ghost") : "none") + "</b></span>";
    }

    // Keep animating while something moves; otherwise sleep until the next input.
    if (visible && !document.hidden && (moving || !reduce)) {
      raf = requestAnimationFrame(frame);
    } else {
      running = false;
    }
  }

  // ---------------------------------------------------------------- sizing & visibility
  function resize() {
    var dpr = Math.min(window.devicePixelRatio || 1, small ? 1.25 : 1.75);
    var w = canvas.clientWidth, h = canvas.clientHeight;
    if (!w || !h) return;
    canvas.width = Math.round(w * dpr);
    canvas.height = Math.round(h * dpr);
    gl.viewport(0, 0, canvas.width, canvas.height);
    setupCamera(w / h);
    wake();
  }
  new ResizeObserver(resize).observe(canvas);
  new IntersectionObserver(function (entries) {
    visible = entries[0].isIntersecting;
    if (visible) wake();
  }).observe(canvas);
  document.addEventListener("visibilitychange", function () {
    if (!document.hidden) wake();
  });

  resize();
  readColors();
  // A first tap so the skin visibly reacts as the page opens.
  setTimeout(function () {
    addRipple(0.3, -0.1, 0.9);
    finger.x = 0.3;
    finger.z = -0.1;
    finger.depth = 0.9;
    wake();
  }, reduce ? 0 : 500);
})();
