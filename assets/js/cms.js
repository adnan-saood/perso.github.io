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
        var label = btn.textContent;
        btn.textContent = getLang() === "fr" ? "Copié !" : "Copied!";
        setTimeout(function () { btn.textContent = label; }, 1500);
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

  // --- "Now" panel: latest public commit on GitHub (cached for an hour) ----------------
  function relTime(iso) {
    var lang = getLang();
    var diff = (new Date(iso).getTime() - Date.now()) / 1000;
    var units = [["year", 31536000], ["month", 2592000], ["week", 604800], ["day", 86400], ["hour", 3600], ["minute", 60]];
    try {
      var rtf = new Intl.RelativeTimeFormat(lang, { numeric: "auto" });
      for (var i = 0; i < units.length; i++) {
        if (Math.abs(diff) >= units[i][1] || i === units.length - 1) return rtf.format(Math.round(diff / units[i][1]), units[i][0]);
      }
    } catch (e) { /* old browser */ }
    return new Date(iso).toLocaleDateString(lang);
  }
  function latestCommit() {
    var tile = document.querySelector("[data-gh-commit]");
    if (!tile) return;
    var user = tile.getAttribute("data-gh-commit");
    var key = "gh:commit:" + user;
    var show = function (c) {
      tile.href = c.url;
      tile.querySelector("[data-commit-msg]").textContent = c.msg;
      tile.querySelector("[data-commit-meta]").textContent = c.repo + " · " + relTime(c.at);
      tile.classList.add("is-live");
    };
    try {
      var hit = JSON.parse(localStorage.getItem(key) || "null");
      if (hit && Date.now() - hit.t < 3600000) return show(hit.c);
    } catch (e) { /* ignore */ }
    var api = "https://api.github.com";
    fetch(api + "/users/" + encodeURIComponent(user) + "/events/public?per_page=30")
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .then(function (events) {
        var push = (events || []).filter(function (ev) { return ev.type === "PushEvent"; })[0];
        if (!push) throw new Error("no push");
        var repo = push.repo.name;
        var commits = push.payload && push.payload.commits;
        // Newer API responses may leave out the commit list: ask for the head commit then.
        var msg = commits && commits.length ? Promise.resolve(commits[commits.length - 1].message)
          : fetch(api + "/repos/" + repo + "/commits/" + push.payload.head).then(function (r) { return r.json(); })
            .then(function (c) { return c.commit.message; });
        return msg.then(function (m) {
          return { msg: String(m).split(/\r?\n/)[0].slice(0, 90), repo: repo.split("/")[1], at: push.created_at,
            url: "https://github.com/" + repo + "/commit/" + push.payload.head };
        });
      })
      .then(function (c) {
        show(c);
        try { localStorage.setItem(key, JSON.stringify({ t: Date.now(), c: c })); } catch (e) { /* ignore */ }
      })
      .catch(function () { /* keep the link to the GitHub profile */ });
  }

  // --- Language: English / French -------------------------------------------------
  function getLang() {
    var m = document.cookie.match(/(?:^|; )lang=(en|fr)/);
    return m ? m[1] : "en";
  }
  function language() {
    var lang = getLang();
    document.documentElement.lang = lang;
    var btn = document.getElementById("lang-toggle");
    if (btn) {
      btn.querySelector("[data-lang-label]").textContent = lang === "fr" ? "EN" : "FR";
      btn.title = lang === "fr" ? "Switch to English" : "Passer en français";
      btn.addEventListener("click", function () {
        var next = getLang() === "fr" ? "en" : "fr";
        document.cookie = "lang=" + next + "; path=" + base + "; max-age=31536000; SameSite=Lax";
        location.reload();
      });
    }
    if (lang !== "fr") return;
    // Static interface text (menu, footer, static pages); PHP pages translate their own content.
    var apply = function (dict) {
      document.querySelectorAll("[data-i18n]").forEach(function (el) {
        var t = dict[el.getAttribute("data-i18n")];
        if (t) el.textContent = t;
      });
    };
    var cached = null;
    try { cached = JSON.parse(localStorage.getItem("cms-i18n") || "null"); } catch (e) { /* ignore */ }
    if (cached && Date.now() - cached.t < 86400000) return apply(cached.d);
    fetch(base + "cms/api.php?i18n=1").then(function (r) { return r.json(); }).then(function (d) {
      apply(d);
      try { localStorage.setItem("cms-i18n", JSON.stringify({ t: Date.now(), d: d })); } catch (e) { /* ignore */ }
    }).catch(function () { /* stay in English */ });
  }

  // --- 3D models (<model-viewer>, loaded only on pages that show a model) -------------
  function models() {
    if (!document.querySelector("model-viewer")) return;
    var s = document.createElement("script");
    s.type = "module";
    s.src = "https://cdn.jsdelivr.net/npm/@google/model-viewer@4.3.1/dist/model-viewer.min.js";
    document.head.appendChild(s);
    // Hide the custom loading bar once the model is on screen (and show it again when it changes).
    document.querySelectorAll("model-viewer").forEach(function (mv) {
      mv.addEventListener("load", function () { mv.classList.add("is-loaded"); });
    });
    // Homepage showcase: thumbnails switch the robot on stage.
    document.querySelectorAll("[data-robots]").forEach(function (box) {
      var viewer = box.querySelector("model-viewer");
      box.addEventListener("click", function (ev) {
        var t = ev.target.closest(".robots__thumb");
        if (!t) return;
        box.querySelectorAll(".robots__thumb").forEach(function (b) {
          b.classList.toggle("is-active", b === t);
          b.setAttribute("aria-selected", b === t ? "true" : "false");
        });
        box.classList.add("is-switching");
        setTimeout(function () {
          if (t.dataset.poster) viewer.setAttribute("poster", t.dataset.poster); else viewer.removeAttribute("poster");
          viewer.classList.remove("is-loaded");
          viewer.setAttribute("src", t.dataset.src);
          viewer.setAttribute("alt", (getLang() === "fr" ? "Modèle 3D : " : "3D model: ") + t.dataset.title);
          box.querySelector("[data-robot-title]").textContent = t.dataset.title;
          box.querySelector("[data-robot-cat]").textContent = t.dataset.cat;
          box.querySelector("[data-robot-text]").textContent = t.dataset.text;
          box.querySelector("[data-robot-link]").href = t.dataset.link;
          box.classList.remove("is-switching");
        }, 250);
      });
    });
  }

  // --- Talks: load the video player only when the visitor clicks play -----------------
  function talks() {
    document.addEventListener("click", function (ev) {
      var btn = ev.target.closest(".talk__video[data-embed]");
      if (!btn) return;
      var frame = document.createElement("iframe");
      frame.src = btn.dataset.embed;
      frame.title = btn.getAttribute("aria-label") || "Video";
      frame.allow = "autoplay; encrypted-media; picture-in-picture; fullscreen";
      frame.allowFullscreen = true;
      frame.className = "talk__frame";
      var wrap = document.createElement("div");
      wrap.className = "talk__media";
      wrap.appendChild(frame);
      btn.replaceWith(wrap);
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    language();
    models();
    talks();
    loadFragments();
    track();
    publications();
    githubCount();
    latestCommit();
  });
})();
