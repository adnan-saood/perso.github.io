// Site interactions: header, mobile menu, scroll reveals, split headings,
// 3D tilt cards, magnetic buttons, counters and project filters.
// Everything is progressive: without JS content is simply static; with reduced
// motion only movement is dropped (see _redesign.scss).
(function () {
  "use strict";
  var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var fine = window.matchMedia("(hover: hover) and (pointer: fine)").matches;
  var root = document.documentElement;
  root.classList.add("js-motion");

  // --- Split headings into words that rise from a mask ----------------------
  function splitText(el) {
    if (el.dataset.splitDone) return;
    el.dataset.splitDone = "1";
    var i = 0;
    (function walk(node) {
      Array.prototype.slice.call(node.childNodes).forEach(function (child) {
        if (child.nodeType === 3) {
          var frag = document.createDocumentFragment();
          child.textContent.split(/(\s+)/).forEach(function (part) {
            if (!part) return;
            if (/^\s+$/.test(part)) {
              frag.appendChild(document.createTextNode(part));
              return;
            }
            var w = document.createElement("span");
            w.className = "w";
            var inner = document.createElement("span");
            inner.textContent = part;
            inner.style.setProperty("--i", i++);
            // Gradient text must live on the moving span or it disappears.
            if (node.classList && node.classList.contains("text-gradient")) inner.className = "text-gradient";
            w.appendChild(inner);
            frag.appendChild(w);
          });
          child.parentNode.replaceChild(frag, child);
        } else if (child.nodeType === 1) {
          walk(child);
          if (child.classList.contains("text-gradient")) child.classList.remove("text-gradient");
        }
      });
    })(el);
  }

  // --- Scroll reveal ---------------------------------------------------------
  var auto = [
    ".post-list > li", ".project-grid > .project-card", ".publications ol.bibliography > li", ".cv .card",
    ".news tr", ".feeds tr", ".post-content > h2", ".post-content > figure", ".post-content > .row",
    ".repositories .repo", ".cms-featured > .cms-card", ".contact-card", ".contact-intro",
  ];
  var io = null;
  function observe(scope) {
    scope = scope || document;
    auto.forEach(function (sel) {
      scope.querySelectorAll(sel).forEach(function (el) { el.classList.add("reveal"); });
    });
    scope.querySelectorAll(".split").forEach(splitText);
    var els = scope.querySelectorAll(".reveal:not(.is-visible), .split:not(.is-visible)");
    if (!("IntersectionObserver" in window)) {
      els.forEach(function (el) { el.classList.add("is-visible"); });
      return;
    }
    if (!io) {
      io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
          if (!e.isIntersecting) return;
          io.unobserve(e.target);
          e.target.classList.add("is-visible");
          if (e.target.hasAttribute("data-count")) countUp(e.target);
          e.target.querySelectorAll("[data-count]").forEach(countUp);
        });
      }, { rootMargin: "0px 0px -8% 0px", threshold: 0.1 });
    }
    // Stagger siblings that appear together.
    var groups = new Map();
    els.forEach(function (el) {
      var p = el.parentNode;
      var n = groups.get(p) || 0;
      groups.set(p, n + 1);
      el.style.setProperty("--reveal-delay", Math.min(n, 8) * 80 + "ms");
      io.observe(el);
    });
    bindTilt(scope);
    bindMagnetic(scope);
    bindFx(scope);
  }
  window.siteMotionObserve = observe; // used after live CMS content loads

  // --- Counters ----------------------------------------------------------------
  function countUp(el) {
    // Keeps any prefix/suffix and decimals of the value typed in the admin ("10+", "3.5", "~40%").
    var m = String(el.getAttribute("data-count")).match(/^(\D*)(\d+(?:[.,]\d+)?)(.*)$/);
    if (!m || reduce) return;
    var target = parseFloat(m[2].replace(",", "."));
    var decimals = (m[2].split(/[.,]/)[1] || "").length;
    var t0 = performance.now(), dur = 1400;
    (function tick(t) {
      var k = Math.min(1, (t - t0) / dur);
      var v = (target * (1 - Math.pow(1 - k, 4))).toFixed(decimals);
      el.textContent = m[1] + (m[2].indexOf(",") !== -1 ? v.replace(".", ",") : v) + m[3];
      if (k < 1) requestAnimationFrame(tick);
    })(t0);
  }

  // --- 3D tilt -----------------------------------------------------------------
  function bindTilt(scope) {
    if (!fine || reduce) return;
    scope.querySelectorAll(".tilt, .project-card").forEach(function (el) {
      if (el.dataset.tiltBound) return;
      el.dataset.tiltBound = "1";
      el.classList.add("tilt");
      var max = el.classList.contains("portrait") ? 6 : 8;
      el.addEventListener("pointermove", function (ev) {
        var r = el.getBoundingClientRect();
        var x = (ev.clientX - r.left) / r.width, y = (ev.clientY - r.top) / r.height;
        el.classList.add("is-tilting");
        el.style.setProperty("--ry", (x - 0.5) * 2 * max + "deg");
        el.style.setProperty("--rx", (0.5 - y) * 2 * max + "deg");
        el.style.setProperty("--mx", x * 100 + "%");
        el.style.setProperty("--my", y * 100 + "%");
      });
      el.addEventListener("pointerleave", function () {
        el.classList.remove("is-tilting");
        el.style.setProperty("--rx", "0deg");
        el.style.setProperty("--ry", "0deg");
      });
    });
  }

  // --- Button touch effect ----------------------------------------------------------
  // Every button reacts like the tactile skin: a fill spreads from where the pointer
  // enters, a patch of "taxels" lights up under it, and a ring ripples out on entry and
  // on press. CSS does the drawing (.fx in _redesign.scss); this only tracks the pointer.
  var FX = {
    solid: ".btn--primary",
    fill: ".btn--ghost",
    soft: ".hero__links a, .proof a, .filters button, .section__more, .pub__link, .now__more, .icon-btn, " +
      ".contact-copy, .site-footer__social a, .robots__thumb, .model__ar, .page-link, .cms-back, .cms-prev-next a, " +
      ".talk__links a, .oss-profile a, .repo-card a.btn",
  };
  function bindFx(scope) {
    Object.keys(FX).forEach(function (kind) {
      scope.querySelectorAll(FX[kind]).forEach(function (el) {
        if (el.classList.contains("fx")) return;
        el.classList.add("fx", "fx--" + kind);
      });
    });
  }
  function fxPoint(el, ev) {
    var r = el.getBoundingClientRect();
    var x = ev.clientX - r.left, y = ev.clientY - r.top;
    el.style.setProperty("--bx", x + "px");
    el.style.setProperty("--by", y + "px");
    return [x, y];
  }
  function fxRing(el, xy, strong) {
    if (reduce) return;
    // One entry ring at a time: magnetic buttons move under the pointer, which would
    // otherwise re-trigger the ring on every edge crossing.
    var now = Date.now();
    if (!strong && now - (el._fxRingAt || 0) < 900) return;
    el._fxRingAt = now;
    var ring = document.createElement("span");
    ring.className = "fx-ring" + (strong ? " fx-ring--press" : "");
    ring.style.left = xy[0] + "px";
    ring.style.top = xy[1] + "px";
    // Big enough to sweep across the whole button from wherever it starts.
    ring.style.setProperty("--fx-ring-size", Math.round(Math.max(el.offsetWidth, el.offsetHeight) * 2.2) + "px");
    el.appendChild(ring);
    ring.addEventListener("animationend", function () { ring.remove(); });
  }
  document.addEventListener("pointerover", function (ev) {
    var el = ev.target.closest && ev.target.closest(".fx");
    if (!el || (ev.relatedTarget && el.contains(ev.relatedTarget))) return;
    fxRing(el, fxPoint(el, ev), false);
  });
  document.addEventListener("pointermove", function (ev) {
    var el = ev.target.closest && ev.target.closest(".fx");
    if (el) fxPoint(el, ev);
  }, { passive: true });
  document.addEventListener("pointerdown", function (ev) {
    var el = ev.target.closest && ev.target.closest(".fx");
    if (el) fxRing(el, fxPoint(el, ev), true);
  });

  // --- Magnetic buttons ----------------------------------------------------------
  function bindMagnetic(scope) {
    if (!fine || reduce) return;
    scope.querySelectorAll(".magnetic").forEach(function (el) {
      if (el.dataset.magBound) return;
      el.dataset.magBound = "1";
      el.addEventListener("pointermove", function (ev) {
        var r = el.getBoundingClientRect();
        el.style.setProperty("--tx", (ev.clientX - r.left - r.width / 2) * 0.25 + "px");
        el.style.setProperty("--ty", (ev.clientY - r.top - r.height / 2) * 0.35 + "px");
      });
      el.addEventListener("pointerleave", function () {
        el.style.setProperty("--tx", "0px");
        el.style.setProperty("--ty", "0px");
      });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    observe(document);
    root.classList.add("motion-ready");

    // --- Header: glass after scrolling, hides on the way down -------------------
    var header = document.getElementById("navbar");
    var bar = document.createElement("div");
    bar.className = "scroll-progress";
    document.body.appendChild(bar);
    var lastY = window.scrollY, ticking = false;
    var hero = document.querySelector(".hero");

    // Keywords strip: speeds up and leans in the direction you scroll, then settles.
    var marquee = document.querySelector(".marquee"), mAnim = null, vel = 0, velRaf = 0;
    function marqueeKick(dy) {
      if (!marquee || reduce) return;
      if (!mAnim) {
        var track = marquee.querySelector(".marquee__track");
        mAnim = track && track.getAnimations ? track.getAnimations()[0] || null : null;
      }
      vel += dy;
      if (!velRaf) velRaf = requestAnimationFrame(settle);
    }
    function settle() {
      vel *= 0.9;
      var v = Math.max(-60, Math.min(60, vel));
      if (mAnim) mAnim.playbackRate = (v < 0 ? -1 : 1) * (1 + Math.abs(v) * 0.12);
      marquee.style.setProperty("--skew", (-v * 0.12).toFixed(2) + "deg");
      if (Math.abs(vel) > 0.3) {
        velRaf = requestAnimationFrame(settle);
      } else {
        velRaf = 0;
        vel = 0;
        if (mAnim) mAnim.playbackRate = 1;
        marquee.style.setProperty("--skew", "0deg");
      }
    }

    function onScroll() {
      var y = window.scrollY;
      marqueeKick(y - lastY);
      // Hero text drifts up and fades a little faster than the page.
      if (hero && !reduce && y < window.innerHeight * 1.3) {
        hero.style.setProperty("--hs", Math.min(1, y / hero.offsetHeight).toFixed(3));
      }
      var max = document.documentElement.scrollHeight - window.innerHeight;
      bar.style.transform = "scaleX(" + (max > 0 ? Math.min(1, y / max) : 0) + ")";
      if (header) {
        header.classList.toggle("nav-scrolled", y > 16);
        var open = root.classList.contains("menu-open");
        if (!reduce && !open && y > 320 && y > lastY + 4) header.classList.add("nav-hidden");
        if (y < lastY - 4 || y < 320) header.classList.remove("nav-hidden");
      }
      lastY = y;
      ticking = false;
    }
    window.addEventListener("scroll", function () {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(onScroll);
      }
    }, { passive: true });
    onScroll();

    // --- Mobile menu ---------------------------------------------------------------
    var menuBtn = document.querySelector(".menu-btn");
    if (menuBtn) {
      var setMenu = function (open) {
        root.classList.toggle("menu-open", open);
        menuBtn.setAttribute("aria-expanded", open ? "true" : "false");
        document.body.style.overflow = open ? "hidden" : "";
      };
      menuBtn.addEventListener("click", function () {
        setMenu(!root.classList.contains("menu-open"));
      });
      document.querySelectorAll(".site-nav a").forEach(function (a) {
        a.addEventListener("click", function () { setMenu(false); });
      });
      document.addEventListener("keydown", function (ev) {
        if (ev.key === "Escape") setMenu(false);
      });
    }

    // --- Project category filters (projects page) ----------------------------------
    document.querySelectorAll("[data-filters]").forEach(function (bar) {
      var grid = document.querySelector(bar.getAttribute("data-filters"));
      bar.addEventListener("click", function (ev) {
        var btn = ev.target.closest("button[data-filter]");
        if (!btn) return;
        bar.querySelectorAll("button").forEach(function (b) { b.classList.toggle("is-active", b === btn); });
        grid.dispatchEvent(new CustomEvent("filtered"));
        var f = btn.getAttribute("data-filter");
        grid.querySelectorAll("[data-category]").forEach(function (card) {
          // data-category may hold several values separated by "|".
          var cats = "|" + (card.getAttribute("data-category") || "") + "|";
          var show = f === "*" || cats.indexOf("|" + f + "|") !== -1;
          card.classList.toggle("is-filtered", !show);
          if (show) card.classList.add("is-visible");
        });
      });
    });
  });
})();
