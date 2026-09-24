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

  // Never count the site owner in the visitor analytics (read by assets/js/cms.js).
  try { localStorage.setItem("cms-no-track", "1"); } catch (e) { /* ignore */ }

  // --- Drag-and-drop reordering for lists marked .sortable ----------------------
  function makeSortable(list, onChange) {
    var dragging = null;
    list.addEventListener("dragstart", function (ev) {
      var item = ev.target.closest("[draggable=true]");
      if (!item) return;
      // Don't hijack text selection inside inputs.
      if (ev.target.closest("input, textarea, select")) { ev.preventDefault(); return; }
      dragging = item;
      item.classList.add("is-dragging");
      ev.dataTransfer.effectAllowed = "move";
      ev.dataTransfer.setData("text/plain", "");
    });
    list.addEventListener("dragover", function (ev) {
      if (!dragging) return;
      ev.preventDefault();
      var over = ev.target.closest("[draggable=true]");
      if (!over || over === dragging || over.parentNode !== list) return;
      var r = over.getBoundingClientRect();
      list.insertBefore(dragging, ev.clientY > r.top + r.height / 2 ? over.nextSibling : over);
    });
    list.addEventListener("dragend", function () {
      if (!dragging) return;
      dragging.classList.remove("is-dragging");
      dragging = null;
      if (onChange) onChange();
    });
  }

  // Publications: homepage order.
  var homeOrder = document.getElementById("home-order");
  if (homeOrder) {
    var dirtyOrder = false;
    makeSortable(homeOrder, function () { dirtyOrder = true; });
    document.querySelector("[data-save-order]").addEventListener("click", function () {
      var fd = new FormData();
      fd.append("action", "pub_home_order");
      fd.append("csrf", homeOrder.dataset.csrf);
      homeOrder.querySelectorAll("[data-slug]").forEach(function (li) { fd.append("order[]", li.dataset.slug); });
      fetch(homeOrder.dataset.saveUrl, { method: "POST", body: fd, credentials: "same-origin" })
        .then(function (r) { return r.json(); })
        .then(function (j) {
          dirtyOrder = false;
          toast("Homepage order saved (" + j.saved + ")");
          homeOrder.querySelectorAll("[data-slug]").forEach(function (li, i) {
            var pill = document.querySelector('#item-list a[href*="slug=' + li.dataset.slug + '"] .pill.gold');
            if (pill) pill.textContent = "homepage #" + (i + 1);
          });
        })
        .catch(function () { alert("Could not save the order."); });
    });
    window.addEventListener("beforeunload", function (ev) { if (dirtyOrder) { ev.preventDefault(); ev.returnValue = ""; } });
  }

  // --- Repeatable rows (CV entries, repositories) --------------------------------
  var rowCounter = 1000;
  document.addEventListener("click", function (ev) {
    var add = ev.target.closest("[data-add-row]");
    if (add) {
      var key = add.dataset.addRow;
      var tpl = document.querySelector('template[data-row-template="' + key + '"]');
      var rows = document.querySelector('[data-rows="' + key + '"]');
      var html = tpl.innerHTML.replace(/__i__/g, "n" + rowCounter++);
      rows.insertAdjacentHTML("beforeend", html);
      var added = rows.lastElementChild;
      added.open = true;
      var first = added.querySelector("input, textarea");
      if (first) first.focus();
      return;
    }
    var remove = ev.target.closest("[data-remove-row]");
    if (remove) {
      ev.preventDefault();
      var row = remove.closest(".row-edit, .repo-row");
      if (row && confirm("Remove this entry? (Nothing is saved until you press Save.)")) row.remove();
      return;
    }
    var move = ev.target.closest("[data-move]");
    if (move) {
      ev.preventDefault();
      var item = move.closest(".row-edit");
      if (move.dataset.move === "-1" && item.previousElementSibling) item.parentNode.insertBefore(item, item.previousElementSibling);
      if (move.dataset.move === "1" && item.nextElementSibling) item.parentNode.insertBefore(item.nextElementSibling, item);
    }
  });
  // Row summaries follow the title field as you type.
  document.addEventListener("input", function (ev) {
    if (!ev.target.matches("[data-row-title]")) return;
    var sum = ev.target.closest(".row-edit").querySelector("summary .grow");
    if (sum) sum.textContent = ev.target.value || "New entry";
  });

  // Repositories editor.
  var repoRows = document.getElementById("repo-rows");
  if (repoRows) {
    makeSortable(repoRows);
    var addInput = document.getElementById("repo-add");
    var userInput = document.querySelector("[data-gh-user]");
    var addRepo = function () {
      var name = addInput.value.trim().replace(/^https?:\/\/github\.com\//, "").replace(/\/$/, "");
      if (name && name.indexOf("/") === -1 && userInput.value) name = userInput.value + "/" + name;
      if (!/^[A-Za-z0-9-]+\/[A-Za-z0-9._-]+$/.test(name)) { toast("Use owner/name, e.g. adnan-saood/hid_ros2"); return; }
      var exists = Array.prototype.some.call(repoRows.querySelectorAll(".repo-row__name"), function (i) { return i.value.toLowerCase() === name.toLowerCase(); });
      if (exists) { toast("Already in the list"); return; }
      var html = document.getElementById("repo-template").innerHTML.replace(/__i__/g, "n" + rowCounter++);
      repoRows.insertAdjacentHTML("afterbegin", html);
      repoRows.firstElementChild.querySelector(".repo-row__name").value = name;
      addInput.value = "";
      toast("Added " + name + " — press Save");
    };
    document.querySelector("[data-repo-add]").addEventListener("click", addRepo);
    addInput.addEventListener("keydown", function (ev) { if (ev.key === "Enter") { ev.preventDefault(); addRepo(); } });
    // Suggest the user's own repositories.
    if (userInput.value) {
      fetch("https://api.github.com/users/" + encodeURIComponent(userInput.value) + "/repos?per_page=100&sort=pushed")
        .then(function (r) { return r.json(); })
        .then(function (list) {
          if (!Array.isArray(list)) return;
          document.getElementById("gh-repos").innerHTML = list.map(function (r) {
            return '<option value="' + r.full_name + '">' + (r.description || "").replace(/[<>"]/g, "") + "</option>";
          }).join("");
        })
        .catch(function () { /* suggestions are optional */ });
    }
  }

  // --- Homepage editor: English / Français tabs -------------------------------------
  var langTabs = document.querySelector("[data-lang-tabs]");
  if (langTabs) {
    langTabs.addEventListener("click", function (ev) {
      var a = ev.target.closest("[data-tab]");
      if (!a) return;
      ev.preventDefault();
      langTabs.querySelectorAll("[data-tab]").forEach(function (t) { t.classList.toggle("on", t === a); });
      document.querySelectorAll("[data-pane]").forEach(function (pane) { pane.hidden = pane.dataset.pane !== a.dataset.tab; });
      document.querySelector("[data-lang-field]").value = a.dataset.tab;
    });
  }

  // --- Uploads & media library (editor, CV) ---------------------------------------
  var form = document.getElementById("editor-form") || document.querySelector("form[data-upload-url]");
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
    if (!mde) return;
    var label = name.replace(/\.[^.]+$/, "").replace(/[-_]+/g, " ");
    mde.codemirror.replaceSelection((isImage ? "!" : "") + "[" + label + "](" + path + ")");
    mde.codemirror.focus();
    dirty = true;
  }

  var dirty = false;
  var mde = document.getElementById("body") && window.EasyMDE ? new EasyMDE({
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
  }) : null;
  if (mde) mde.codemirror.on("change", function () { dirty = true; });
  form.addEventListener("input", function () { dirty = true; });
  form.addEventListener("submit", function () { dirty = false; });
  window.addEventListener("beforeunload", function (ev) {
    if (dirty) { ev.preventDefault(); ev.returnValue = ""; }
  });
  document.addEventListener("keydown", function (ev) {
    if ((ev.ctrlKey || ev.metaKey) && ev.key.toLowerCase() === "s") {
      ev.preventDefault();
      if (mde) mde.codemirror.save();
      dirty = false;
      form.requestSubmit ? form.requestSubmit() : form.submit();
    }
  });

  // --- Share image: a branded 1200x630 card generated in the browser on save ---------
  // (the server has no image library). Used when the page is shared on LinkedIn etc.
  function slugify(t) {
    return t.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "").slice(0, 80);
  }
  function loadImage(src) {
    return new Promise(function (resolve) {
      if (!src) return resolve(null);
      var img = new Image();
      var done = false;
      img.onload = function () { done = true; resolve(img); };
      img.onerror = function () { done = true; resolve(null); };
      img.src = /^https?:|^\//.test(src) ? src : (form.dataset.base || "/") + src;
      setTimeout(function () { if (!done) resolve(null); }, 4000);
    });
  }
  function wrapText(ctx, text, maxWidth, maxLines) {
    var words = text.split(/\s+/), lines = [], line = "";
    words.forEach(function (w) {
      var test = line ? line + " " + w : w;
      if (ctx.measureText(test).width > maxWidth && line) { lines.push(line); line = w; } else { line = test; }
    });
    if (line) lines.push(line);
    if (lines.length > maxLines) {
      lines = lines.slice(0, maxLines);
      lines[maxLines - 1] = lines[maxLines - 1].replace(/\s+\S*$/, "") + "…";
    }
    return lines;
  }
  function shareImage(title, kicker, coverSrc) {
    return loadImage(coverSrc).then(function (cover) {
      var W = 1200, H = 630, c = document.createElement("canvas");
      c.width = W; c.height = H;
      var g = c.getContext("2d");
      var grad = g.createLinearGradient(0, 0, W, H);
      grad.addColorStop(0, "#6a45ff"); grad.addColorStop(1, "#ff6f4e");
      g.fillStyle = grad; g.fillRect(0, 0, W, H);
      var textW = 1060;
      if (cover) {
        // Cover picture on the right, fading into the gradient.
        var cw = 470, ch = H, sx = 0, sy = 0, sw = cover.width, sh = cover.height;
        if (sw / sh > cw / ch) { sw = sh * cw / ch; sx = (cover.width - sw) / 2; } else { sh = sw * ch / cw; sy = (cover.height - sh) / 2; }
        g.drawImage(cover, sx, sy, sw, sh, W - cw, 0, cw, ch);
        var fade = g.createLinearGradient(W - cw, 0, W - cw + 220, 0);
        fade.addColorStop(0, "rgba(140,85,220,1)"); fade.addColorStop(1, "rgba(140,85,220,0)");
        g.fillStyle = fade; g.fillRect(W - cw, 0, 220, H);
        textW = 640;
      } else {
        // Taxel grid with a pressure bloom, like the homepage skin.
        for (var x = 660; x < W; x += 26) for (var y = 26; y < H; y += 26) {
          var p = Math.max(0, 1 - Math.hypot(x - 930, y - 310) / 240);
          g.fillStyle = "rgba(255,255,255," + (0.18 + p * 0.7) + ")";
          g.beginPath(); g.arc(x, y, 2.2 + p * 4.5, 0, Math.PI * 2); g.fill();
        }
        textW = 600;
      }
      g.fillStyle = "rgba(255,255,255,.85)";
      g.font = "600 24px ui-monospace, Consolas, monospace";
      g.fillText(kicker.toUpperCase(), 72, 110);
      g.fillStyle = "#fff";
      var size = title.length > 70 ? 54 : 64;
      g.font = "700 " + size + "px 'Segoe UI', system-ui, sans-serif";
      wrapText(g, title, textW, 4).forEach(function (l, i) { g.fillText(l, 68, 190 + i * size * 1.15); });
      g.font = "700 28px 'Segoe UI', system-ui, sans-serif";
      g.fillStyle = "rgba(255,255,255,.18)";
      var name = form.dataset.site || "";
      var nw = g.measureText(name).width + 48;
      g.beginPath(); g.roundRect ? g.roundRect(72, 532, nw, 52, 26) : g.rect(72, 532, nw, 52); g.fill();
      g.fillStyle = "#fff"; g.fillText(name, 96, 568);
      return new Promise(function (resolve) { c.toBlob(resolve, "image/png"); });
    });
  }
  if (form.id === "editor-form" && form.dataset.og === "1") {
    var ogDone = false;
    var kinds = { posts: "Blog post", news: "News", projects: "Project", talks: "Talk" };
    form.addEventListener("submit", function (ev) {
      var titleEl = form.elements.title;
      if (ogDone || !titleEl || !titleEl.value.trim()) return;
      ev.preventDefault();
      var slugEl = form.elements.slug;
      var slugVal = (slugEl && slugEl.value.trim()) || slugify(titleEl.value);
      var cover = (form.elements.img || form.elements.thumbnail || form.elements.image || {}).value || "";
      shareImage(titleEl.value.trim(), (kinds[form.dataset.type] || "") + " · " + (form.dataset.site || ""), cover)
        .then(function (blob) {
          if (!blob) return;
          var fd = new FormData();
          fd.append("action", "og_upload");
          fd.append("csrf", form.dataset.csrf);
          fd.append("type", form.dataset.type);
          fd.append("slug", slugVal);
          fd.append("image", blob, "share.png");
          return fetch(form.dataset.uploadUrl, { method: "POST", body: fd, credentials: "same-origin" })
            .then(function (r) { return r.json(); })
            .then(function (j) { if (j.url) form.elements.og_image.value = j.url; });
        })
        .catch(function () { /* the item is saved even if the share image fails */ })
        .then(function () {
          ogDone = true;
          dirty = false;
          if (mde) mde.codemirror.save();
          form.submit();
        });
    });
  }

  // Slug follows the title until you edit it yourself.
  var title = form.querySelector('[name="title"]');
  var slug = form.querySelector('[name="slug"]');
  var slugTouched = !slug || slug.value !== "";
  if (slug) slug.addEventListener("input", function () { slugTouched = true; });
  if (title) title.addEventListener("input", function () {
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
        toast("Selected " + path.split("/").pop());
      });
    });
  });

  // "Upload" buttons that fill a URL field (cover image).
  document.querySelectorAll("[data-upload-into]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var picker = document.createElement("input");
      picker.type = "file";
      picker.accept = btn.dataset.accept || "image/*";
      picker.onchange = function () {
        if (!picker.files.length) return;
        btn.disabled = true;
        upload(picker.files[0]).then(function (url) {
          document.querySelector(btn.dataset.uploadInto).value = url;
          dirty = true;
          toast("Uploaded " + url.split("/").pop());
        }, function (e) { alert(e.message); }).then(function () { btn.disabled = false; });
      };
      picker.click();
    });
  });
})();
