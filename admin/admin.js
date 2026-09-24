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

  // Copy a ready-made Markdown snippet (Files page).
  document.querySelectorAll("[data-copy-text]").forEach(function (b) {
    b.addEventListener("click", function () {
      navigator.clipboard.writeText(b.dataset.copyText).then(function () { toast("Markdown copied — paste it into a post or project"); });
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

  // --- Media library picker ---------------------------------------------------
  // Browse the Files folders, upload by dropping, click to insert or select.
  var picker = (function () {
    var dlg = document.getElementById("media");
    if (!dlg || typeof dlg.showModal !== "function") return null;
    var grid = dlg.querySelector(".media__grid");
    var crumbs = dlg.querySelector(".media__crumbs");
    var status = dlg.querySelector(".media__status");
    var fileInput = dlg.querySelector(".media__upload input");
    var src = "files";          // "files" (uploads) or "site" (assets/img, read-only)
    var dirs = { files: "", site: "" }; // last folder per library
    var current = "";
    var writable = true;
    var onPick = null;
    var uploadBtn = dlg.querySelector(".media__upload");
    var hint = dlg.querySelector(".media__hint");

    function el(tag, cls, text) {
      var n = document.createElement(tag);
      if (cls) n.className = cls;
      if (text !== undefined) n.textContent = text;
      return n;
    }

    function load(dir) {
      current = dir;
      dirs[src] = dir;
      status.textContent = "Loading…";
      fetch(dlg.dataset.listUrl + "&src=" + src + "&dir=" + encodeURIComponent(dir), { credentials: "same-origin" })
        .then(function (r) { return r.json(); })
        .then(render)
        .catch(function () { status.textContent = "Could not load the files."; });
    }

    function render(d) {
      if (d.error) { status.textContent = d.error; return; }
      status.textContent = "";
      writable = !!d.writable;
      uploadBtn.hidden = !writable;
      hint.textContent = writable
        ? "Click a picture to insert it. Drop files anywhere here to upload them into this folder."
        : "Pictures that ship with the site (assets/img, read-only). Click one to insert it.";
      dlg.querySelectorAll("[data-media-src]").forEach(function (b) { b.classList.toggle("on", b.dataset.mediaSrc === src); });
      crumbs.innerHTML = "";
      var parts = d.dir ? d.dir.split("/") : [];
      var acc = "";
      var rootBtn = el("button", "", src === "site" ? "assets/img" : "files");
      rootBtn.type = "button";
      rootBtn.dataset.dir = "";
      crumbs.appendChild(rootBtn);
      parts.forEach(function (part) {
        acc = acc ? acc + "/" + part : part;
        crumbs.appendChild(document.createTextNode(" / "));
        var b = el("button", "", part);
        b.type = "button";
        b.dataset.dir = acc;
        crumbs.appendChild(b);
      });

      grid.innerHTML = "";
      if (d.parent !== null) {
        var up = el("button", "media__item is-dir");
        up.type = "button";
        up.dataset.dir = d.parent;
        up.appendChild(el("span", "media__thumb", "↩"));
        up.appendChild(el("span", "media__name", "Back"));
        grid.appendChild(up);
      }
      d.dirs.forEach(function (f) {
        var b = el("button", "media__item is-dir");
        b.type = "button";
        b.dataset.dir = f.rel;
        b.appendChild(el("span", "media__thumb", "📁"));
        b.appendChild(el("span", "media__name", f.name));
        grid.appendChild(b);
      });
      d.files.forEach(function (f) {
        var b = el("button", "media__item");
        b.type = "button";
        b.dataset.path = f.path;
        b.dataset.name = f.name;
        b.dataset.image = f.image ? "1" : "";
        var t = el("span", "media__thumb");
        if (f.image) {
          var img = el("img");
          img.src = f.url;
          img.alt = "";
          img.loading = "lazy";
          t.appendChild(img);
        } else {
          t.textContent = (f.name.split(".").pop() || "file").toUpperCase();
        }
        b.appendChild(t);
        b.appendChild(el("span", "media__name", f.name));
        b.appendChild(el("span", "media__size", f.size));
        grid.appendChild(b);
      });
      if (!d.dirs.length && !d.files.length) {
        grid.appendChild(el("p", "media__empty", writable ? "This folder is empty. Drop pictures here to upload them." : "This folder is empty."));
      }
    }

    function uploadFiles(list) {
      if (!list.length) return;
      if (!writable) {
        status.textContent = "Site images are read-only. Switch to Uploads to add pictures.";
        return;
      }
      status.textContent = "Uploading " + list.length + " file" + (list.length > 1 ? "s" : "") + "…";
      Promise.all(Array.prototype.map.call(list, shrink)).then(function (files) {
        var fd = new FormData();
        fd.append("action", "media_upload");
        fd.append("csrf", dlg.dataset.csrf);
        fd.append("dir", current);
        files.forEach(function (f) { fd.append("files[]", f, f.name); });
        return fetch(dlg.dataset.uploadUrl, { method: "POST", body: fd, credentials: "same-origin" });
      }).then(function (r) { return r.json(); }).then(function (j) {
        load(current);
        setTimeout(function () {
          status.textContent = (j.uploaded && j.uploaded.length ? j.uploaded.length + " uploaded. " : "") + (j.errors || []).join(" ");
        }, 300);
      }).catch(function () { status.textContent = "Upload failed."; });
    }

    dlg.addEventListener("click", function (ev) {
      if (ev.target === dlg || ev.target.closest("[data-media-close]")) { dlg.close(); return; }
      var tab = ev.target.closest("[data-media-src]");
      if (tab) { src = tab.dataset.mediaSrc; load(dirs[src]); return; }
      var dirBtn = ev.target.closest("[data-dir]");
      if (dirBtn) { load(dirBtn.dataset.dir); return; }
      var fileBtn = ev.target.closest("[data-path]");
      if (fileBtn && onPick) {
        onPick(fileBtn.dataset.path, fileBtn.dataset.name, fileBtn.dataset.image === "1");
        dlg.close();
      }
    });
    fileInput.addEventListener("change", function () {
      uploadFiles(fileInput.files);
      fileInput.value = "";
    });
    ["dragenter", "dragover"].forEach(function (e) {
      dlg.addEventListener(e, function (ev) { ev.preventDefault(); if (writable) dlg.classList.add("is-over"); });
    });
    dlg.addEventListener("dragleave", function (ev) {
      if (!ev.relatedTarget || !dlg.contains(ev.relatedTarget)) dlg.classList.remove("is-over");
    });
    dlg.addEventListener("drop", function (ev) {
      ev.preventDefault();
      dlg.classList.remove("is-over");
      uploadFiles(ev.dataTransfer.files);
    });

    return {
      open: function (cb) {
        onPick = cb;
        dlg.showModal();
        load(dirs[src]);
      },
    };
  })();

  function insertFile(path, name, isImage) {
    var label = name.replace(/\.[^.]+$/, "").replace(/[-_]+/g, " ");
    mde.codemirror.replaceSelection((isImage ? "!" : "") + "[" + label + "](" + path + ")");
    mde.codemirror.focus();
    dirty = true;
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
      "link", "upload-image",
      { name: "library", className: "fa fa-folder-open", title: "Insert from media library",
        action: function () { if (picker) picker.open(insertFile); } },
      "table", "code", "horizontal-rule", "|", "preview", "side-by-side", "fullscreen", "|", "guide"],
    sideBySideFullscreen: false,
    // Content uses base-free paths ("files/…", "assets/…"); resolve them for the preview.
    previewRender: function (text) {
      return this.parent.markdown(text).replace(/(src|href)="((?:assets|files)\/[^"]*)"/g, function (m, attr, path) {
        return attr + '="' + (form.dataset.base || "/") + path + '"';
      });
    },
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
    slug.placeholder = title.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "")
      .replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "").slice(0, 80) || "auto from title";
  });

  // "Library" buttons that fill a URL field from the media library.
  document.querySelectorAll("[data-library-into]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      if (!picker) return;
      picker.open(function (path) {
        document.querySelector(btn.dataset.libraryInto).value = path;
        dirty = true;
        toast("Cover image set");
      });
    });
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
