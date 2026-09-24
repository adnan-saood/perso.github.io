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
  }
  window.siteMotionObserve = observe; // used after live CMS content loads

  // --- Counters ----------------------------------------------------------------
  function countUp(el) {
    var target = parseFloat(el.getAttribute("data-count"));
    if (isNaN(target) || reduce) return;
    var t0 = performance.now(), dur = 1400;
    (function tick(t) {
      var k = Math.min(1, (t - t0) / dur);
      el.textContent = Math.round(target * (1 - Math.pow(1 - k, 4)));
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
    function onScroll() {
      var y = window.scrollY;
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
        var f = btn.getAttribute("data-filter");
        grid.querySelectorAll(".project-card").forEach(function (card) {
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
