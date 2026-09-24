// Connects static Jekyll pages to the PHP CMS:
//  - fills [data-cms-fragment] placeholders (homepage projects, publications, news, posts)
//  - privacy-friendly visitor analytics beacon (cms/hit.php; no cookies)
//  - publication search / BibTeX copy, live GitHub repository count
(function () {
  "use strict";
  var base = document.documentElement.dataset.baseurl || "/";

  function loadFragments() {
    document.querySelectorAll("[data-cms-fragment]").forEach(function (el) {
      var type = el.dataset.cmsFragment;
      var url = base + "cms/api.php?fragment=" + encodeURIComponent(type) +
        "&limit=" + encodeURIComponent(el.dataset.limit || "5");
      fetch(url, { credentials: "omit" })
        .then(function (r) { if (!r.ok) throw new Error(r.status); return r.text(); })
        .then(function (html) {
          // Without PHP (e.g. `jekyll serve`) the server returns the script's source; never show that.
          if (html.trim().indexOf("<!--cms-->") !== 0) throw new Error("not a fragment");
          el.innerHTML = html;
          el.classList.add("cms-loaded");
          if (window.siteMotionObserve) window.siteMotionObserve(el);
        })
        .catch(function () {
          var link = { posts: "blog/", projects: "projects/", publications: "publications/" }[type] || "news/";
          var a = '<a href="' + base + link + '">See all ' + (type === "news" ? "updates" : type) + " &rarr;</a>";
          el.innerHTML = el.tagName === "TBODY" ? "<tr><td>" + a + "</td></tr>" : el.tagName === "OL" ? "<li>" + a + "</li>" : "<p>" + a + "</p>";
        });
    });
  }

  // --- Analytics ---------------------------------------------------------------
  // Counts a page view (and time on page when leaving). Skipped for the site owner
  // (the admin panel sets "cms-no-track") and for visitors who ask not to be tracked.
  function track() {
    var optOut = false;
    try { optOut = localStorage.getItem("cms-no-track") === "1"; } catch (e) { /* ignore */ }
    if (optOut || navigator.doNotTrack === "1" || navigator.globalPrivacyControl) return;
    if (!navigator.sendBeacon) return;
    var endpoint = base + "cms/hit.php";
    var url = location.pathname + location.search;
    var send = function (data) {
      navigator.sendBeacon(endpoint, new Blob([JSON.stringify(data)], { type: "text/plain" }));
    };
    var tz = "";
    try { tz = Intl.DateTimeFormat().resolvedOptions().timeZone || ""; } catch (e) { /* ignore */ }
    send({ u: url, r: document.referrer, w: window.innerWidth, l: navigator.language || "", z: tz });

    // Time actually spent on the page (only while it is visible).
    var visibleSince = document.visibilityState === "visible" ? Date.now() : 0;
    var spent = 0;
    var sent = false;
    document.addEventListener("visibilitychange", function () {
      if (document.visibilityState === "visible") {
        visibleSince = Date.now();
      } else {
        if (visibleSince) spent += Date.now() - visibleSince;
        visibleSince = 0;
        if (!sent && spent > 1000) {
          sent = true;
          send({ e: "leave", u: url, s: Math.round(spent / 1000) });
        }
      }
    });
  }

  // --- Publications: search box + BibTeX copy -------------------------------------
  function publications() {
    var input = document.querySelector("[data-pub-search]");
    var list = document.getElementById("pub-list");
    if (input && list) {
      var apply = function () {
        var q = input.value.trim().toLowerCase();
        var any = false;
        list.querySelectorAll(".pub").forEach(function (li) {
          var match = !q || (li.dataset.search || "").indexOf(q) !== -1;
          li.classList.toggle("is-searched-out", !match);
          if (match && !li.classList.contains("is-filtered")) any = true;
        });
        list.querySelectorAll(".pub-year").forEach(function (sec) {
          sec.hidden = !sec.querySelector(".pub:not(.is-filtered):not(.is-searched-out)");
        });
        var empty = list.querySelector(".pub-empty");
        if (empty) empty.hidden = any;
      };
      input.addEventListener("input", apply);
      list.addEventListener("filtered", apply);
    }
    document.addEventListener("click", function (ev) {
      var btn = ev.target.closest("[data-copy-bib]");
      if (!btn) return;
      var pre = btn.parentNode.querySelector("pre");
      navigator.clipboard.writeText(pre.textContent).then(function () {
        btn.textContent = "Copied!";
        setTimeout(function () { btn.textContent = "Copy"; }, 1500);
      });
    });
  }

  // --- Live GitHub public-repository count (homepage stat) ---------------------------
  function githubCount() {
    document.querySelectorAll("[data-gh-repos]").forEach(function (el) {
      var user = el.getAttribute("data-gh-repos");
      var key = "gh:count:" + user;
      var show = function (n) {
        el.setAttribute("data-count", n);
        el.textContent = n;
      };
      try {
        var hit = JSON.parse(localStorage.getItem(key) || "null");
        if (hit && Date.now() - hit.t < 3600000) return show(hit.n);
      } catch (e) { /* ignore */ }
      fetch("https://api.github.com/users/" + encodeURIComponent(user))
        .then(function (r) { return r.json(); })
        .then(function (u) {
          if (typeof u.public_repos !== "number") return;
          show(u.public_repos);
          try { localStorage.setItem(key, JSON.stringify({ t: Date.now(), n: u.public_repos })); } catch (e) { /* ignore */ }
        })
        .catch(function () { el.textContent = "30+"; });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    loadFragments();
    track();
    publications();
    githubCount();
  });
})();
