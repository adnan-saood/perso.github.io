// Site-wide motion: scroll reveals, glass navbar, reading progress, card spotlight.
// Everything is progressive: without JS (or with reduced motion) content is simply static.
(function () {
  "use strict";
  var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var root = document.documentElement;
  root.classList.add("js-motion");

  // --- Scroll reveal --------------------------------------------------------
  // Tag common content blocks so every page gets entrance animations without
  // having to edit each template.
  var auto = [
    ".post-list > li", ".projects .card", ".publications ol.bibliography > li", ".cv .card",
    ".news tr", ".post-content > h2", ".post-content > figure", ".post-content > .row",
    ".repositories .repo", ".home-publications",
  ];
  function tag(scope) {
    auto.forEach(function (sel) {
      (scope || document).querySelectorAll(sel).forEach(function (el) { el.classList.add("reveal"); });
    });
  }

  var io = null;
  function observe(scope) {
    tag(scope);
    var els = (scope || document).querySelectorAll(".reveal:not(.is-visible)");
    if (reduce || !("IntersectionObserver" in window)) {
      els.forEach(function (el) { el.classList.add("is-visible"); });
      return;
    }
    if (!io) {
      io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
          if (!e.isIntersecting) return;
          io.unobserve(e.target);
          e.target.classList.add("is-visible");
        });
      }, { rootMargin: "0px 0px -8% 0px", threshold: 0.08 });
    }
    // Stagger siblings that enter together.
    var groups = new Map();
    els.forEach(function (el) {
      var p = el.parentNode;
      var i = groups.get(p) || 0;
      groups.set(p, i + 1);
      el.style.setProperty("--reveal-delay", Math.min(i, 8) * 60 + "ms");
      io.observe(el);
    });
  }
  window.siteMotionObserve = observe; // used after live content loads

  document.addEventListener("DOMContentLoaded", function () {
    observe(document);
    root.classList.add("motion-ready");

    // --- Navbar: glass effect + hide on scroll down, show on scroll up -------
    var nav = document.getElementById("navbar");
    var bar = document.createElement("div");
    bar.className = "scroll-progress";
    document.body.appendChild(bar);
    var lastY = window.scrollY;
    var ticking = false;
    function onScroll() {
      var y = window.scrollY;
      var max = document.documentElement.scrollHeight - window.innerHeight;
      bar.style.transform = "scaleX(" + (max > 0 ? Math.min(1, y / max) : 0) + ")";
      if (nav) {
        nav.classList.toggle("nav-scrolled", y > 12);
        var menuOpen = nav.querySelector(".navbar-collapse.show");
        nav.classList.toggle("nav-hidden", !reduce && !menuOpen && y > 240 && y > lastY + 4);
        if (y < lastY - 4) nav.classList.remove("nav-hidden");
      }
      lastY = y;
      ticking = false;
    }
    window.addEventListener("scroll", function () {
      if (!ticking) { ticking = true; requestAnimationFrame(onScroll); }
    }, { passive: true });
    onScroll();

    // --- Cursor spotlight on cards ------------------------------------------
    if (!reduce && window.matchMedia("(hover: hover)").matches) {
      document.addEventListener("pointermove", function (ev) {
        var card = ev.target.closest && ev.target.closest(".card, .cms-card, .contact-card, .home-news");
        if (!card) return;
        var r = card.getBoundingClientRect();
        card.style.setProperty("--mx", ev.clientX - r.left + "px");
        card.style.setProperty("--my", ev.clientY - r.top + "px");
      }, { passive: true });
    }
  });
})();
