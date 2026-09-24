// Fills [data-cms-fragment] placeholders (homepage projects, news, latest posts)
// on static Jekyll pages with live content from the PHP CMS.
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
          var link = { posts: "blog/", projects: "projects/" }[type] || "news/";
          var a = '<a href="' + base + link + '">See the latest ' + (type === "projects" ? "projects" : "updates") + " &rarr;</a>";
          el.innerHTML = el.tagName === "TBODY" ? "<tr><td>" + a + "</td></tr>" : "<p>" + a + "</p>";
        });
    });
  }

  document.addEventListener("DOMContentLoaded", loadFragments);
})();
