// /repositories/: enriches the statically rendered page with live GitHub data.
// Three API calls per visit (profile, repositories, public events), cached for an hour
// in localStorage so reloads don't eat into GitHub's 60-requests-per-hour limit.
(function () {
  "use strict";
  var root = document.querySelector("[data-github-user]");
  if (!root) return;
  var user = root.getAttribute("data-github-user");
  var API = "https://api.github.com";
  var TTL = 60 * 60 * 1000;

  // GitHub's own language colours for the languages that appear on this profile.
  var COLORS = {
    "C++": "#f34b7d", C: "#555555", Python: "#3572A5", MATLAB: "#e16737", CMake: "#DA3434",
    JavaScript: "#f1e05a", TypeScript: "#3178c6", HTML: "#e34c26", CSS: "#563d7c", SCSS: "#c6538c",
    Shell: "#89e051", Makefile: "#427819", "Jupyter Notebook": "#DA5B0B", TeX: "#3D6117", Cuda: "#3A4E3A",
    Rust: "#dea584", Go: "#00ADD8", Java: "#b07219", Ruby: "#701516", PHP: "#4F5D95",
  };
  function color(lang) { return COLORS[lang] || "var(--ink-3)"; }

  function cached(url) {
    var key = "gh:" + url;
    try {
      var hit = JSON.parse(localStorage.getItem(key) || "null");
      if (hit && Date.now() - hit.t < TTL) return Promise.resolve(hit.d);
    } catch (e) { /* storage unavailable */ }
    return fetch(url, { headers: { Accept: "application/vnd.github+json" } }).then(function (r) {
      if (!r.ok) throw new Error("GitHub " + r.status);
      return r.json();
    }).then(function (d) {
      try { localStorage.setItem(key, JSON.stringify({ t: Date.now(), d: d })); } catch (e) { /* ignore */ }
      return d;
    });
  }

  function ago(iso) {
    var s = (Date.now() - new Date(iso).getTime()) / 1000;
    var units = [["year", 31536000], ["month", 2592000], ["week", 604800], ["day", 86400], ["hour", 3600], ["minute", 60]];
    for (var i = 0; i < units.length; i++) {
      var n = Math.floor(s / units[i][1]);
      if (n >= 1) return n + " " + units[i][0] + (n > 1 ? "s" : "");
    }
    return "moments";
  }
  function set(name, value) {
    root.querySelectorAll('[data-gh="' + name + '"]').forEach(function (el) { el.textContent = value; });
  }
  function countTo(name, n) {
    root.querySelectorAll('[data-gh="' + name + '"]').forEach(function (el) {
      el.setAttribute("data-count", n);
      el.textContent = n;
    });
  }
  function esc(s) {
    return String(s).replace(/[&<>"]/g, function (c) { return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]; });
  }

  // ---------------------------------------------------------------- profile
  cached(API + "/users/" + user).then(function (u) {
    if (u.name) set("name", u.name);
    if (u.bio) set("bio", u.bio.replace(/\s+/g, " "));
    countTo("followers", u.followers);
    countTo("repos", u.public_repos);
    set("since", ago(u.created_at));
  }).catch(function () { /* keep static values */ });

  // ---------------------------------------------------------------- repositories
  var cards = Array.prototype.slice.call(root.querySelectorAll(".repo-card"));
  var grid = root.querySelector("#repo-grid");

  cached(API + "/users/" + user + "/repos?per_page=100&sort=pushed").then(function (repos) {
    var own = repos.filter(function (r) { return !r.fork; });
    var byName = {};
    repos.forEach(function (r) { byName[r.full_name.toLowerCase()] = r; });

    // Headline numbers
    var stars = own.reduce(function (a, r) { return a + r.stargazers_count; }, 0);
    countTo("stars", stars);
    var last = own.reduce(function (a, r) { return r.pushed_at > a ? r.pushed_at : a; }, "");
    if (last) set("lastpush", ago(last));

    // Language mix of your own repositories
    var langs = {};
    own.forEach(function (r) { if (r.language) langs[r.language] = (langs[r.language] || 0) + 1; });
    var list = Object.keys(langs).map(function (k) { return [k, langs[k]]; }).sort(function (a, b) { return b[1] - a[1]; });
    var total = list.reduce(function (a, l) { return a + l[1]; }, 0);
    countTo("languages", list.length);
    if (total) {
      var bar = root.querySelector('[data-gh="langbar"]');
      var legend = root.querySelector('[data-gh="langlegend"]');
      bar.innerHTML = list.map(function (l) {
        return '<span style="--w:' + (l[1] / total * 100).toFixed(2) + "%;--c:" + color(l[0]) + '" title="' + esc(l[0]) + '"></span>';
      }).join("");
      legend.innerHTML = list.map(function (l) {
        return '<li><span class="dot" style="background:' + color(l[0]) + '"></span>' + esc(l[0]) +
          " <b>" + Math.round(l[1] / total * 100) + "%</b></li>";
      }).join("");
      root.querySelector(".oss-langs").hidden = false;
    }

    // Enrich each featured card
    cards.forEach(function (card) {
      var r = byName[card.getAttribute("data-repo").toLowerCase()];
      if (!r) return;
      var desc = card.querySelector(".repo-card__desc");
      if (r.description && r.description.trim().length > 12 && !desc.getAttribute("data-note")) desc.textContent = r.description.trim();
      if (!desc.textContent.trim() && r.description) desc.textContent = r.description.trim();
      if (r.language) {
        card.querySelector('[data-f="lang"]').textContent = r.language;
        card.querySelector(".repo-card__lang .dot").style.background = color(r.language);
      }
      if (r.stargazers_count) {
        var s = card.querySelector('[data-f="stars"]');
        s.hidden = false;
        s.querySelector("b").textContent = r.stargazers_count;
      }
      if (r.forks_count) {
        var f = card.querySelector('[data-f="forks"]');
        f.hidden = false;
        f.querySelector("b").textContent = r.forks_count;
      }
      card.querySelector('[data-f="updated"]').textContent = "updated " + ago(r.pushed_at) + " ago";
      if (Date.now() - new Date(r.pushed_at).getTime() < 30 * 86400000) card.querySelector(".repo-card__live").hidden = false;
      var topics = (r.topics || []).slice(0, 3);
      if (topics.length) {
        card.querySelector(".repo-card__tags").insertAdjacentHTML("beforeend",
          topics.map(function (t) { return '<span class="chip chip--topic">#' + esc(t) + "</span>"; }).join(""));
      }
      card.dataset.stars = r.stargazers_count;
      card.dataset.updated = r.pushed_at;
    });
    root.classList.add("is-live");
  }).catch(function () {
    root.classList.add("is-offline");
  });

  // ---------------------------------------------------------------- sorting
  var sort = root.querySelector("[data-oss-sort]");
  if (sort) {
    sort.addEventListener("change", function () {
      var by = sort.value;
      cards.slice().sort(function (a, b) {
        if (by === "stars") return (+b.dataset.stars || 0) - (+a.dataset.stars || 0);
        if (by === "updated") return (b.dataset.updated || "").localeCompare(a.dataset.updated || "");
        if (by === "name") return a.querySelector(".repo-card__name").textContent.localeCompare(b.querySelector(".repo-card__name").textContent);
        return a.dataset.order - b.dataset.order;
      }).forEach(function (c) { grid.appendChild(c); });
    });
  }

  // ---------------------------------------------------------------- activity
  var VERBS = {
    PushEvent: function (e) {
      var n = (e.payload && e.payload.size) || (e.payload && e.payload.commits ? e.payload.commits.length : 0);
      return ["ti-git-commit", "Pushed " + (n ? n + " commit" + (n > 1 ? "s" : "") + " to" : "to")];
    },
    CreateEvent: function (e) { return ["ti-plus", "Created " + (e.payload.ref_type === "repository" ? "repository" : e.payload.ref_type + " in")]; },
    PullRequestEvent: function (e) { return ["ti-git-pull-request", (e.payload.action === "closed" ? "Closed" : "Opened") + " a pull request in"]; },
    IssuesEvent: function (e) { return ["ti-circle-dot", (e.payload.action === "closed" ? "Closed" : "Opened") + " an issue in"]; },
    WatchEvent: function () { return ["ti-star", "Starred"]; },
    ForkEvent: function () { return ["ti-git-fork", "Forked"]; },
    ReleaseEvent: function () { return ["ti-tag", "Published a release of"]; },
    PublicEvent: function () { return ["ti-world", "Open-sourced"]; },
  };
  cached(API + "/users/" + user + "/events/public?per_page=30").then(function (events) {
    var items = [];
    var seen = {};
    events.forEach(function (e) {
      var verb = VERBS[e.type];
      if (!verb || items.length >= 8) return;
      var v = verb(e);
      var key = v[1] + e.repo.name + e.created_at.slice(0, 10);
      if (seen[key]) return; // collapse repeated pushes on the same day
      seen[key] = true;
      items.push('<li class="reveal"><span class="oss-timeline__icon"><i class="ti ' + v[0] + '"></i></span>' +
        '<span class="oss-timeline__text">' + esc(v[1]) + ' <a href="https://github.com/' + esc(e.repo.name) + '" rel="noopener">' +
        esc(e.repo.name.replace(user + "/", "")) + "</a></span>" +
        '<time datetime="' + esc(e.created_at) + '">' + ago(e.created_at) + " ago</time></li>");
    });
    if (!items.length) return;
    var list = root.querySelector('[data-gh="activity"]');
    list.innerHTML = items.join("");
    root.querySelector(".oss-activity").hidden = false;
    if (window.siteMotionObserve) window.siteMotionObserve(root.querySelector(".oss-activity"));
  }).catch(function () { /* no activity section */ });
})();
