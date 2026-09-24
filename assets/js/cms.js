// Connects static Jekyll pages to the PHP CMS:
//  - fills [data-cms-fragment] placeholders (homepage news / latest posts)
//  - sends the contact form without a page reload
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

  function contactForm() {
    var form = document.getElementById("contact-form");
    if (!form) return;
    var status = form.querySelector(".cf-status");
    var button = form.querySelector("button[type=submit]");
    form.querySelector("[name=t]").value = Date.now();

    var sent = new URLSearchParams(location.search).get("sent");
    if (sent === "1") { status.textContent = "Thanks! Your message was sent."; status.className = "cf-status ok"; }
    if (sent === "0") { status.textContent = "Sorry, something went wrong. Please check the form and try again."; status.className = "cf-status err"; }

    form.addEventListener("submit", function (ev) {
      ev.preventDefault();
      if (!form.checkValidity()) { form.reportValidity(); return; }
      button.disabled = true;
      form.classList.add("sending");
      status.textContent = "Sending…";
      status.className = "cf-status";
      fetch(form.action, { method: "POST", body: new FormData(form), headers: { Accept: "application/json" } })
        .then(function (r) { return r.json(); })
        .then(function (j) {
          if (!j.ok) throw new Error(j.error || "Something went wrong.");
          form.reset();
          form.classList.add("sent");
          status.textContent = "Thanks! Your message was sent. I'll get back to you soon.";
          status.className = "cf-status ok";
        })
        .catch(function (e) {
          status.textContent = e.message && e.message.length < 200 ? e.message : "Sorry, the message could not be sent.";
          status.className = "cf-status err";
        })
        .then(function () {
          button.disabled = false;
          form.classList.remove("sending");
          form.querySelector("[name=t]").value = Date.now();
        });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    loadFragments();
    contactForm();
  });
})();
