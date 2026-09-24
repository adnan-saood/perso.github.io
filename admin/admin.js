(function () {
  "use strict";

  function toast(msg) {
    var t = document.createElement("div");
    t.className = "toast";
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(function () { t.remove(); }, 2200);
  }

  // Confirm before destructive submits.
  document.querySelectorAll("form[data-confirm]").forEach(function (f) {
    f.addEventListener("submit", function (ev) {
      if (!confirm(f.dataset.confirm)) ev.preventDefault();
    });
  });

  // Forms that ask for a value first (new folder, rename).
  document.querySelectorAll("form[data-prompt]").forEach(function (f) {
    f.addEventListener("submit", function (ev) {
      var v = prompt(f.dataset.prompt, f.dataset.promptDefault || "");
      if (!v) { ev.preventDefault(); return; }
      f.querySelector('[name="' + f.dataset.promptField + '"]').value = v;
    });
  });

  // Live filter for lists.
  document.querySelectorAll("[data-filter]").forEach(function (input) {
    var list = document.querySelector(input.dataset.filter);
    input.addEventListener("input", function () {
      var q = input.value.toLowerCase();
      list.querySelectorAll(".row").forEach(function (r) {
        r.style.display = r.textContent.toLowerCase().indexOf(q) === -1 ? "none" : "";
      });
    });
  });

  // Copy public link of a file.
  document.querySelectorAll("[data-copy]").forEach(function (b) {
    b.addEventListener("click", function () {
      var url = new URL(b.dataset.copy, location.href).href;
      navigator.clipboard.writeText(url).then(function () { toast("Link copied"); });
    });
  });

  // File manager: drag & drop uploads and bulk selection.
  var dz = document.getElementById("dropzone");
  if (dz) {
    var input = dz.querySelector("input[type=file]");
    ["dragenter", "dragover"].forEach(function (e) {
      document.addEventListener(e, function (ev) { ev.preventDefault(); dz.classList.add("over"); });
    });
    ["dragleave", "drop"].forEach(function (e) {
      document.addEventListener(e, function (ev) {
        if (e === "dragleave" && ev.relatedTarget) return;
        dz.classList.remove("over");
      });
    });
    document.addEventListener("drop", function (ev) {
      ev.preventDefault();
      if (!ev.dataTransfer.files.length) return;
      input.files = ev.dataTransfer.files;
      dz.classList.add("busy");
      dz.submit();
    });
    input.addEventListener("change", function () {
      if (input.files.length) { dz.classList.add("busy"); dz.submit(); }
    });
  }
  var bulk = document.getElementById("bulk");
  if (bulk) {
    var bar = bulk.querySelector(".bulkbar");
    var boxes = document.querySelectorAll('input[form="bulk"]');
    boxes.forEach(function (b) {
      b.addEventListener("change", function () {
        var n = document.querySelectorAll('input[form="bulk"]:checked').length;
        bar.hidden = n === 0;
        bar.querySelector("[data-count]").textContent = n;
      });
    });
  }

  // Editor.
  var form = document.getElementById("editor-form");
  if (!form) return;

  // Big photos are scaled down in the browser (the server has no image library):
  // max 2400px on the long edge, re-encoded as JPEG (or WebP if it may have transparency).
  var MAX_EDGE = 2400;
  function shrink(file) {
    if (!/^image\/(jpeg|png|webp)$/.test(file.type) || !window.createImageBitmap) return Promise.resolve(file);
    return createImageBitmap(file).then(function (img) {
      var scale = Math.min(1, MAX_EDGE / Math.max(img.width, img.height));
      if (scale === 1 && file.size < 1.5 * 1024 * 1024) return file;
      var canvas = document.createElement("canvas");
      canvas.width = Math.round(img.width * scale);
      canvas.height = Math.round(img.height * scale);
      canvas.getContext("2d").drawImage(img, 0, 0, canvas.width, canvas.height);
      var type = file.type === "image/jpeg" ? "image/jpeg" : "image/webp";
      return new Promise(function (resolve) {
        canvas.toBlob(function (blob) {
          if (!blob || blob.size >= file.size) return resolve(file);
          var name = file.name.replace(/\.[^.]+$/, "") + (type === "image/jpeg" ? ".jpg" : ".webp");
          resolve(new File([blob], name, { type: type }));
        }, type, 0.85);
      });
    }, function () { return file; });
  }

  function upload(file) {
    return shrink(file).then(send);
  }

  function send(file) {
    var fd = new FormData();
    fd.append("action", "upload_image");
    fd.append("csrf", form.dataset.csrf);
    fd.append("image", file);
    return fetch(form.dataset.uploadUrl, { method: "POST", body: fd, credentials: "same-origin" })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (j.error) throw new Error(j.error);
        return j.url;
      });
  }

  var dirty = false;
  var mde = new EasyMDE({
    element: document.getElementById("body"),
    autoDownloadFontAwesome: false,
    spellChecker: false,
    nativeSpellcheck: true,
    status: ["lines", "words"],
    minHeight: "420px",
    uploadImage: true,
    imageAccept: "image/png, image/jpeg, image/gif, image/webp, image/avif",
    imageUploadFunction: function (file, onSuccess, onError) {
      upload(file).then(onSuccess, function (e) { onError(e.message); });
    },
    toolbar: ["bold", "italic", "heading-2", "heading-3", "|", "quote", "unordered-list", "ordered-list", "|",
      "link", "upload-image", "table", "code", "horizontal-rule", "|", "preview", "side-by-side", "fullscreen", "|", "guide"],
    sideBySideFullscreen: false,
  });
  mde.codemirror.on("change", function () { dirty = true; });
  form.addEventListener("input", function () { dirty = true; });
  form.addEventListener("submit", function () { dirty = false; });
  window.addEventListener("beforeunload", function (ev) {
    if (dirty) { ev.preventDefault(); ev.returnValue = ""; }
  });
  document.addEventListener("keydown", function (ev) {
    if ((ev.ctrlKey || ev.metaKey) && ev.key.toLowerCase() === "s") {
      ev.preventDefault();
      mde.codemirror.save();
      dirty = false;
      form.requestSubmit ? form.requestSubmit() : form.submit();
    }
  });

  // Slug follows the title until you edit it yourself.
  var title = form.querySelector('[name="title"]');
  var slug = form.querySelector('[name="slug"]');
  var slugTouched = slug.value !== "";
  slug.addEventListener("input", function () { slugTouched = true; });
  title.addEventListener("input", function () {
    if (slugTouched) return;
    slug.placeholder = title.value.toLowerCase().normalize("NFD").replace(/[̀-ͯ]/g, "")
      .replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "").slice(0, 80) || "auto from title";
  });

  // "Upload" buttons that fill a URL field (cover image).
  document.querySelectorAll("[data-upload-into]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var picker = document.createElement("input");
      picker.type = "file";
      picker.accept = "image/*";
      picker.onchange = function () {
        if (!picker.files.length) return;
        btn.disabled = true;
        upload(picker.files[0]).then(function (url) {
          document.querySelector(btn.dataset.uploadInto).value = url;
          dirty = true;
          toast("Image uploaded");
        }, function (e) { alert(e.message); }).then(function () { btn.disabled = false; });
      };
      picker.click();
    });
  });
})();
