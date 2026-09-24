// Fills [data-cms-fragment] placeholders (homepage news / latest posts)
// on static Jekyll pages with live content from the PHP CMS.
(function () {
  "use strict";
  var base = document.documentElement.dataset.baseurl || "/";

  function loadFragments() {
    document.querySelectorAll("[data-cms-fragment]").forEach(function (el) {
      var url = base + "cms/api.php?fragment=" + encodeURIComponent(el.dataset.cmsFragment) +
        "&limit=" + encodeURIComponent(el.dataset.limit || "5");
      fetch(url, { credentials: "omit" })
        .then(function (r) { if (!r.ok) throw new Error(r.status); return r.text(); })
        .then(function (html) {
          el.innerHTML = html;
          el.classList.add("cms-loaded");
          if (window.siteMotionObserve) window.siteMotionObserve(el);
        })
        .catch(function () {
          var link = el.dataset.cmsFragment === "posts" ? "blog/" : "news/";
          el.innerHTML = '<tr><td><a href="' + base + link + '">See the latest updates &rarr;</a></td></tr>';
        });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    loadFragments();
  });
})();
