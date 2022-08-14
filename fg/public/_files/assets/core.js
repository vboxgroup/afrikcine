! function(w) {
  function x(a = _c.current_dir.path, b = _c.current_dir.basename, d) {
    var c = (_c.file_names || []).length;
    document.title = _c.config.title ? _c.config.title(a, b, d, c) : _c.title.replace("%name%", b || "/").replace("%path%", a).replace("%count%", c)
  }

  window.postMsg = function (t){
      window.parent.postMessage({
        filesGalleryHref: t.getAttribute('href'),
        filesGalleryIsImage: t.getAttribute('data-isimage'),
        filesGalleryBasename: t.getAttribute('data-basename'),
        filesGalleryExt: t.getAttribute('data-ext'),
      }, "*");
  }

  function y(a) {
    return a ? a.replace(/"/g, "&quot;") : ""
  }

  function z(a) {
    return a ? a.replace(/</g, "&lt;").replace(/>/g, "&gt;") : ""
  }

  function A(a, b) {
    return '<span class="' + b + '">' + a + "</span>"
  }

  function B(a) {
    return al ? _c.script + "?download_dir_zip=" + encodeURIComponent(a.path) + "&" + a.mtime : "#"
  }
  _c.config = Object.assign({
    favicon: "<link rel=\"icon\" href=\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%2337474F' d='M20,18H4V8H20M20,6H12L10,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V8C22,6.89 21.1,6 20,6Z' /%3E%3C/svg%3E\" type=\"image/svg+xml\" />",
    title: ("%name% [%count%]" === _c.title || !_c.title) && ((d, a, b, c) => (a || "/") + (b ? "" : " [" + c + "]")),
    panorama: {
      is_pano(a, d) {
        var b = a.dimensions[0],
          h = a.dimensions[1],
          c = d.max_texture_size;
        if (!(c < 2048 || b < 2048 || b / h != 2)) {
          if (!a.panorama_resized) return c >= b && D(a);
          var e = [b].concat(a.panorama_resized),
            f = e.pop(),
            i = screen.availWidth * d.pixel_ratio * 6;
          if (!(f > c)) {
            var g = e.find(a => c >= a && a < i) || f;
            return g === b ? D(a) : a.url_path.replace(a.basename, "_files_" + g + "_" + a.basename)
          }
        }
      }
    },
    history_scroll: !0,
    load_svg_max_filesize: 1e5
  }, _c.config || {}), "isConnected" in Node.prototype || Object.defineProperty(Node.prototype, "isConnected", {
    get() {
      return this.ownerDocument.contains(this)
    }
  });
  var a, d, g, b, c, k, h, r, l, m, C = {
    a: function(a, c, b) {
      return a ? '<a class="' + c + ' map-link" target="_blank" href="' + J(a) + '" data-lang="google maps"' + (b ? "" : ' title="' + aj.get("google maps") + '"') + ">" + (b ? aj.get("google maps") : f.get_svg_icon("marker")) + "</a>" : ""
    },
    span: function(a, b) {
      return a ? '<span class="' + b + ' map-link" data-href="' + J(a) + '"' + O("google maps") + ">" + f.get_svg_icon("marker") + "</span>" : ""
    }
  };

  function D(a, c) {
    var b = !!a.url_path && encodeURI(a.url_path).replace(/#/g, "%23");
    return a.is_dir ? b || "#" : !b || c && ["php", "htaccess"].includes(a.ext) || _c.load_files_proxy_php ? _c.script + (c ? "?download=" : "?file=") + encodeURIComponent(a.path) : b
  }

  function E(a, b) {
    return a.is_dir ? F(a.path) : D(a, b)
  }

  function F(a) {
    return location.pathname + (a ? "?" + encodeURIComponent(a).replace(/%2F/g, "/") : "")
  }

  function G(a) {
    for (; a.firstChild;) a.removeChild(a.firstChild)
  }

  function H(a, b) {
    a.length && i(a, function(a) {
      (b || a.parentNode).removeChild(a)
    })
  }

  function I(a, c, b) {
    s(a, function(a) {
      var b = a.target.dataset.action;
      b && c(b, a)
    }, "click", !1, b)
  }

  function J(a) {
    return Array.isArray(a) ? "https://www.google.com/maps/search/?api=1&query=" + a : "#"
  }

  function K(a, b) {
    return a ? '<span class="' + b + '">' + a[0] + " x " + a[1] + "</span>" : ""
  }

  function L(a, b) {
    return a.is_dir ? a.hasOwnProperty("dirsize") ? '<span class="' + b + '">' + filesize(a.dirsize) + "</span>" : "" : '<span class="' + b + '">' + filesize(a.filesize) + "</span>"
  }

  function M(a, b) {
    return _c.context_menu && a ? '<span class="context-button ' + b + '" data-action="context">' + f.get_svg_icon_multi("dots", "minus") + "</span>" : ""
  }

  function _(a, b, g, h) {
    if (!a || !a.iptc) return "";
    var d = Object.keys(a.iptc);
    if (!d.length) return "";
    var c = "",
      e = "",
      f = "";
    return d.forEach(function(d) {
      var g = a.iptc[d];
      if (g) {
        if (["city", "sub-location", "province-state"].includes(d)) return e += '<span class="' + b + "-" + d + '">' + g + "</span>";
        if (["creator", "credit", "copyright"].includes(d)) return f += '<span class="' + b + "-" + d + '">' + g + "</span>";
        if ("keywords" === d && Array.isArray(g)) {
          var h = g.filter(a => a);
          return c += h.length ? '<div class="' + b + "-" + d + '">' + h.join(", ") + "</div>" : ""
        }
        return c += '<div class="' + b + "-" + d + '">' + g + "</div>"
      }
    }), (c += (e ? '<div class="' + b + '-location">' + e + "</div>" : "") + (f ? '<div class="' + b + '-owner">' + f + "</div>" : "")) ? g ? '<div class="' + b + '-iptc">' + c + "</div>" : c : ""
  }

  function N(a, c, d) {
    if (!a || !a.exif) return "";
    var b = V(["Model", "ApertureFNumber", "FocalLength", "ExposureTime", "ISOSpeedRatings", "gps"], function(c) {
      var b = a.exif[c];
      if (!b) return "";
      if ("Model" === c) b = f.get_svg_icon(b.toLowerCase().indexOf("phone") > -1 ? "cellphone" : "camera") + b;
      else if ("FocalLength" === c) {
        var e = b.split("/");
        2 === e.length && (b = (e[0] / e[1]).toFixed(1) + "<small>mm</small>")
      } else if ("gps" === c) return C[d || "a"](b, "exif-item exif-gps");
      return '<span class="exif-item exif-' + c + '"' + O(c) + ">" + b + "</span>"
    });
    return b ? '<div class="' + c + '">' + b + "</div>" : ""
  }

  function O(a, b) {
    return a && e.is_pointer ? ' data-lang="' + a + '"' + (b ? ' data-tooltip="' : ' title="') + aj.get(a, !b) + '"' : ""
  }

  function P(c) {
    if (navigator.clipboard) return navigator.clipboard.writeText(c);
    var a = document.createElement("span");
    a.textContent = c, a.style.whiteSpace = "pre", document.body.appendChild(a);
    var b = window.getSelection(),
      d = window.document.createRange();
    b.removeAllRanges(), d.selectNode(a), b.addRange(d);
    var e = !1;
    try {
      e = window.document.execCommand("copy")
    } catch (f) {
      console.log("error", f)
    }
    return b.removeAllRanges(), window.document.body.removeChild(a), e ? Promise.resolve() : Promise.reject()
  }

  function Q(a, b, d) {
    if (d || a.which > 1 || a.metaKey || a.ctrlKey || a.shiftKey || a.altKey) {
      var c = !!b && b.getAttribute("href");
      if (c && "#" !== c) return b.contains(a.target) || b.click(), !0
    }
    a.preventDefault()
  }

  function s(c, a, d, e, f) {
    var b, g, h;
    c.addEventListener(d || "click", (b = a, (g = f) ? function(a) {
      h || (b.apply(this, arguments), h = setTimeout(function() {
        h = null
      }, g))
    } : b)), e && a()
  }

  function R(a, b) {
    var c;
    return function(d) {
      c && clearTimeout(c), c = setTimeout(a, b || 1e3, d)
    }
  }

  function S(c, d, a, b) {
    return b && (a = R(a, b)), c.addEventListener(d, a), {
      remove: function() {
        c.removeEventListener(d, a)
      }
    }
  }

  function T(b, c, a) {
    var d = a ? "add" : "remove";
    i(U(b, c, !a), function(a) {
      a.classList[d](c)
    })
  }

  function U(a, b, c) {
    return a.filter(function(a) {
      return c == a.classList.contains(b)
    })
  }

  function i(b, c) {
    for (var d = b.length, a = 0; a < d; a++) c(b[a], a)
  }

  function V(b, d) {
    for (var c = "", e = b.length, a = 0; a < e; a++) c += d(b[a], a) || "";
    return c
  }

  function n(d, a, b) {
    var e = new RegExp("[" + (b ? "#" : "?") + "&]" + d + (a ? "=([^&]*)" : "($|&|=)")),
      c = location[b ? "hash" : "search"].match(e);
    return !!c && (!a || c[1])
  }

  function o(a) {
    _c.debug && console.log.apply(this, arguments)
  }

  function W(a, b) {
    a && !a.style.display != !b && (a.style.display = b ? "none" : null)
  }

  function X(a, b, c) {
    u.plugins.mousetrap.loaded && Mousetrap[3 === arguments.length ? "bind" : "unbind"].apply(null, arguments)
  }
  _id = document.getElementById.bind(document), _class = function(a, b) {
    return Array.from((b || document).getElementsByClassName(a))
  }, _tag = function(a, b) {
    return Array.from((b || document).getElementsByTagName(a))
  }, _query = function(a, b) {
    return (b || document).querySelector(a)
  }, _querya = function(a, b) {
    return Array.from((b || document).querySelectorAll(a))
  };
  var j = function() {
    function a(a) {
      return e.local_storage ? localStorage.getItem(a) : null
    }

    function b(b, a) {
      "boolean" == typeof a && (a = a.toString());
      try {
        localStorage.setItem(b, a)
      } catch (c) {
        o("failed to write localstorage", c, "warn")
      }
    }
    return {
      get: function(c) {
        var b = a(c);
        return "true" === b || "false" !== b && b
      },
      get_json: function(c) {
        var b = a(c);
        if (b) try {
          return JSON.parse(b)
        } catch (d) {}
        return null
      },
      set: function(a, c, f, d) {
        return e.local_storage ? f && !c ? localStorage.removeItem(a) : d ? setTimeout(function() {
          b(a, c)
        }, d) : void b(a, c) : null
      },
      remove: function(a) {
        if (e.local_storage) return localStorage.removeItem(a)
      }
    }
  }();

  function Y(b) {
    var a = new XMLHttpRequest;
    return a.onreadystatechange = function() {
      if (4 == a.readyState) {
        if (b.always && b.always(a), 200 == a.status) {
          var e = a.responseText,
            c = b.json_response,
            d = c ? function() {
              try {
                return JSON.parse(e)
              } catch (a) {
                return c = !1, e
              }
            }() : e;
          if (c && d.error && "login" === d.error) ap.fire(aj.get("login", !0) + "!").then(a => {
            a.isConfirmed && location.reload()
          });
          else {
            b.complete && b.complete(d, e, c);
            var f = !b.url && a.getResponseHeader("files-msg");
            f && o("XHR: files-msg: " + f)
          }
        } else b.fail && b.fail(a)
      }
    }, a.open(b.params ? "POST" : "GET", b.url || _c.script), b.params && a.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"), b.json_response && a.setRequestHeader("Accept", "application/json"), a.send(b.params || null), a
  }

  function Z(a) {
    return _c.server_exif && a && a.exif && a.exif.Orientation && a.exif.Orientation > 4 && a.exif.Orientation < 9
  }

  function t(a) {
    return atob(a)
  }

  function aa(a, b, c) {
    return Math.min(Math.max(c, a), b)
  }

  function ab() {
    if (e.scrollbar_width) {
      var a = document.documentElement,
        b = window.innerWidth > a.clientWidth ? a.getBoundingClientRect().width : 0;
      b ? b !== af.body_width && a.style.setProperty("--body-width", b + "px") : af.body_width && a.style.removeProperty("--body-width"), af.body_width = b
    }
  }
  var ac = {
    store: function(a) {
      a.dataset.tooltipOriginal || (a.dataset.tooltipOriginal = a.dataset.tooltip)
    },
    set: function(a, b, c) {
      ac.store(a), a.dataset.tooltip = aj.get(b), c && a.classList.add("show-tooltip")
    },
    timer: function(a, b, c, d) {
      b && ac.store(a), b && (a.dataset.tooltip = aj.get(b)), c && a.classList.add("tooltip-" + c), a.classList.add("show-tooltip"), setTimeout(function() {
        b && (a.dataset.tooltip = a.dataset.tooltipOriginal || ""), c && a.classList.remove("tooltip-" + c), a.classList.remove("show-tooltip")
      }, d || 1e3)
    }
  };

  function ad(b) {
    if (!(b.is_dir && b.is_readable && _c.folder_preview_image && _c.load_images && _c.image_resize_enabled)) return "";
    var a = _c.dirs[b.path],
      c = !1;
    if (a && a.hasOwnProperty("preview")) {
      if (!a.preview) return;
      a.files && a.files[a.preview] && (c = "?file=" + encodeURIComponent(b.path + "/" + a.preview) + "&resize=" + _c.image_resize_dimensions)
    }
    return c || (c = "?preview=" + encodeURIComponent(b.path)), '<img data-src="' + _c.script + c + "&" + _c.image_cache_hash + "." + b.mtime + '" class="files-folder-preview files-lazy">'
  }
  var ae = {
    popup(c, a, b, d, e) {
      c && c.preventDefault(), a = Math.floor(Math.min(screen.width, a || 1e3)), b = Math.floor(Math.min(screen.height, b || 99999));
      var f = window.open(d, e || null, "toolbar=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,titlebar=no,width=" + a + ",height=" + b + ",top=" + Math.round(screen.height / 2 - b / 2) + ",left=" + Math.round(screen.width / 2 - a / 2));
      window.focus && f.focus()
    },
    gaga() {
      console.log("ladida")
    }
  };
  _c.debug = n("debug") || 0 === location.host.indexOf("files.test"), _c.files = {}, o("_c", _c);
  var f = {},
    af = {},
    u = {},
    ag = {
      main: _id("main"),
      topbar: _id("topbar"),
      files_container: _id("files-container"),
      files: _id("files"),
      topbar_info: _id("topbar-info"),
      filter_container: _id("search-container"),
      filter: _id("search"),
      modal: _id("files_modal"),
      modal_bg: _id("modal-bg")
    },
    e = {};

  function ah(a, b) {
    if (b.mime1 && b.mime0 === a) return e.hasOwnProperty(a) || (e[a] = function() {
      if ("audio" === a && !window.Audio) return !1;
      var c = "audio" === a ? ["mpeg", "mp4", "x-aiff", "ogg", "x-m4a", "aac", "webm", "wave", "wav", "x-wav", "x-pn-wav", "flac"] : ["mp4", "webm", "ogg", "3gp", "m4v", "x-m4v"];
      try {
        var d = document.createElement(a);
        if (!d.canPlayType) return !1;
        var b = c.filter(function(b) {
          return d.canPlayType(a + "/" + b).replace(/no/, "")
        });
        return !!b.length && b
      } catch (e) {
        return !1
      }
    }()), !(!e[a] || !e[a].includes(b.mime1)) && b.mime1
  }

  function ai(a) {
    return a[0].toUpperCase() + a.slice(1)
  }
  a = e, g = (d = document).documentElement, b = navigator, c = window, a.explorer = /MSIE /.test(b.userAgent) || /Trident\//.test(b.userAgent), k = !!(c.CSS && c.CSS.supports || c.supportsCSS), !a.explorer && k && CSS.supports("color", "var(--fake-var)") || (d.body.innerHTML = '<div class="alert alert-danger" role="alert"><h4 class="alert-heading">' + (a.explorer ? "Internet Explorer" : "This browser is") + ' not supported.</h4>Please use a modern browser like <a href="https://www.microsoft.com/en-us/windows/microsoft-edge" class="alert-link">Edge</a>, <a href="https://www.google.com/chrome/" class="alert-link">Chrome</a>, <a href="https://www.mozilla.org/firefox/" class="alert-link">Firefox</a>, <a href="https://www.opera.com/" class="alert-link">Opera</a> or <a href="https://www.apple.com/safari/" class="alert-link">Safari</a>.</div>', d.body.classList.remove("body-loading"), fail), a.local_storage = !!c.localStorage && function() {
      try {
        var a = "_t";
        return c.localStorage.setItem(a, a), c.localStorage.removeItem(a), !0
      } catch (b) {
        return !1
      }
    }(), a.is_touch = "ontouchstart" in c || b.maxTouchPoints > 0 || b.msMaxTouchPoints > 0 || c.DocumentTouch && d instanceof DocumentTouch || c.matchMedia("(any-pointer: coarse)").matches, a.is_pointer = !a.is_touch || matchMedia("(pointer:fine)").matches, a.is_dual_input = a.is_touch && a.is_pointer, a.only_touch = a.is_touch && !a.is_pointer, a.only_pointer = !a.is_touch && a.is_pointer, a.PointerEvent = !!c.PointerEvent || b.msPointerEnabled, a.nav_langs = !(!b.languages || !b.languages.length) && b.languages || !!b.language && [b.language], a.pointer_events = "PointerEvent" in c || b.msPointerEnabled, a.is_mac = b.platform.toUpperCase().indexOf("MAC") >= 0, a.is_mac && g.style.setProperty("--mac-bold", "500"), a.c_key = a.is_mac ? "\xe2\u0152\u02DC" : "ctrl-", a.scrollbar_width = a.is_pointer ? function() {
      var b = c.innerWidth - g.clientWidth;
      if (b) return b;
      var a = d.createElement("div");
      d.body.appendChild(a), a.style.cssText = "width: 100px;height: 100px;overflow: scroll;position: absolute;top: -9999px";
      var e = a.offsetWidth - a.clientWidth;
      return d.body.removeChild(a), e
    }() : 0, a.scrollbar_width && g.classList.add("has-scrollbars"), a.pixel_ratio = c.devicePixelRatio || 1, a.download = "download" in d.createElement("a"), a.clipboard = !(!d.queryCommandSupported || !d.queryCommandSupported("copy")), a.url = !("function" != typeof URL), a.fullscreen = screenfull.isEnabled, a.image_orientation = CSS.supports("image-orientation", "from-image"), a.browser_images = ["jpg", "jpeg", "png", "gif", "bmp", "svg", "svg+xml", "ico", "vnd.microsoft.icon", "x-icon"], (h = new Image).onload = h.onerror = function() {
      2 == h.height && a.browser_images.push("webp"), a.webp = 2 == h.height
    }, h.src = "data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA", a.history = !(!c.history || !history.pushState), a.history || (_c.history = !1), location.search && "URLSearchParams" in c && new URLSearchParams(location.search).forEach(function(a, b) {
      a && b.startsWith("--") && g.style.setProperty(b, a)
    }), a.max_texture_size = function() {
      if (c.WebGLRenderingContext) {
        var a = document.createElement("canvas");
        if (a && a.getContext) try {
          var b = a.getContext("webgl") || a.getContext("experimental-webgl");
          return !!b && b.getParameter(b.MAX_TEXTURE_SIZE)
        } catch (d) {
          return
        }
      }
    }() || 0, o("tests", e),
    function() {
      if (e.local_storage) {
        var a = n("clearall", !0),
          b = !a && n("clear", !0),
          c = a || b;
        if (f.clean_localstorage = function() {
            if (!c) {
              var a = Object.keys(localStorage);
              a.length && i(a, function(a) {
                if (a.startsWith("files:menu:")) localStorage.removeItem(a);
                else if (a.startsWith("files:dir:")) {
                  if (a.startsWith("files:dir:" + _c.dirs_hash)) {
                    if (_c.exists) {
                      var c = a.split(":"),
                        b = c[3];
                      if (_c.menu_max_depth && b.split("/").length >= _c.menu_max_depth) return;
                      var d = parseInt(c[4]);
                      _c.dirs[b] && _c.dirs[b].mtime == d || localStorage.removeItem(a)
                    }
                  } else localStorage.removeItem(a)
                }
              })
            }
          }, c) {
          var d = 0;
          i(Object.keys(localStorage), function(b) {
            (a && b.startsWith("files:") || b.startsWith("files:menu:") || b.startsWith("files:dir:")) && (localStorage.removeItem(b), d++)
          }), o(d + " localStorage items cleared")
        } else _c.menu_exists || f.clean_localstorage()
      }
    }(), e.local_storage && "clear_storage" === n("action", !0) && i(Object.keys(localStorage), function(a) {
      (a.startsWith("files:config:") || a.startsWith("files:interface:")) && localStorage.removeItem(a)
    }), r = {}, (l = ["layout", "sort", "menu_show"]).forEach(function(a) {
      r[a] = _c[a]
    }), f.set_config = function(a, b) {
      if (r.hasOwnProperty(a)) {
        if (_c[a] = b, r[a] === b) return j.remove("files:config:" + a);
        j.set("files:config:" + a, b)
      }
    }, (m = j.get_json("files:options:" + _c.location_hash)) && (i(Object.keys(m), function(a) {
      f.set_config(a, m[a])
    }), j.remove("files:options:" + _c.location_hash), j.remove("files:ls_options")), i(l, function(a) {
      var b = j.get("files:config:" + a);
      if (null !== b) return b === _c[a] ? j.remove("files:config:" + a) : void(_c[a] = b)
    });
  var aj = function() {
    var q = !1,
      r = !1,
      d = _c.config && _c.config.lang || {},
      k = {
        bg: null,
        cs: null,
        da: null,
        de: null,
        en: null,
        es: null,
        et: null,
        fr: null,
        hu: null,
        it: null,
        ja: null,
        ko: null,
        nl: null,
        no: null,
        pl: null,
        pt: null,
        ro: null,
        ru: null,
        sk: null,
        th: null,
        zh: null
      },
      s = {
        cs: "cz",
        da: "dk",
        en: "gb",
        et: "ee",
        ja: "jp",
        ko: "kr",
        sv: "se",
        vi: "vn",
        zh: "cn"
      },
      g = "object" == typeof _c.lang_custom ? _c.lang_custom : {};
    "object" == typeof d.langs && Object.keys(d.langs).forEach(a => {
      g[a] = Object.assign(g[a] || {}, d.langs[a])
    }), Object.keys(g).forEach(a => {
      g[a].flag && (s[a] = g[a].flag), k.hasOwnProperty(a) || (k[a] = g[a])
    });
    var l = Object.keys(k).sort();
    if (e.local_storage) {
      var a = j.get("files:version");
      a !== _c.version && j.set("files:version", _c.version), a && a !== _c.version && i(l, function(a) {
        j.remove("files:lang:" + a)
      })
    }
    var m = {},
      o = {
        get: function(a, c) {
          var b = m[a] || a;
          return c ? ai(b) : b
        },
        set: function(a, b) {
          a.dataset.lang = b, a.textContent = this.get(b)
        },
        span: function(a, b) {
          return '<span data-lang="' + a + '" class="no-pointer">' + this.get(a, b) + "</span>"
        },
        dropdown: function() {
          var a = n("lang_menu", !0) || d.menu;
          if (a && "false" != a && "0" != a) {
            var b = Array.isArray(d.menu) ? d.menu : l;
            ag.topbar_top.insertAdjacentHTML("beforeend", '<div id="change-lang" class="dropdown' + (q ? " dropdown-lang-loading" : "") + '"><button type="button" class="btn-icon btn-topbar btn-lang" data-text="' + _.split("-")[0] + '"></button><div class="dropdown-menu dropdown-menu-topbar dropdown-menu-left"><h6 class="dropdown-header" data-lang="language">' + o.get("language") + '</h6><div class="dropdown-lang-items">' + V(b, function(a) {
              return '<button class="dropdown-item-lang' + (a === _ ? " dropdown-lang-active" : "") + '" data-action="' + a + '"><img src="https://cdn.jsdelivr.net/npm/flag-icons@6.1.1/flags/1x1/' + (s[a] || a) + '.svg" class="dropdown-lang-flag"></button>'
            }) + "</div></div>");
            var g = (r = ag.topbar_top.lastElementChild).firstElementChild,
              c = r.lastElementChild.lastElementChild,
              e = b.indexOf(_),
              h = e > -1 && c.children[e];
            f.dropdown(r, g), I(c, function(a, b) {
              a !== _ && (_ = a, p(a), f.dayjs_locale(a), u.uppy && f.uppy_locale(a), j.set("files:lang:current", a), g.dataset.text = a.split("-")[0], h && h.classList.remove("dropdown-lang-active"), (h = b.target).classList.add("dropdown-lang-active"))
            })
          }
        }
      };

    function p(a) {
      if ("en" === a) return v({}, a);
      var b, c = k[a] || j.get_json("files:lang:" + a);
      return c ? v(c, a) : (b = a, void(t(!0), Y({
        url: _c.assets + "lang/" + b + ".json",
        json_response: !0,
        complete: function(a, c, d) {
          t(), a && c && d && (j.set("files:lang:" + b, c), v(a, b))
        },
        fail: function() {
          t()
        }
      })))
    }

    function t(a) {
      q = !!a, r && r.classList.toggle("dropdown-lang-loading", q)
    }

    function v(b, a) {
      k[a] || (k[a] = Object.assign(b, g[a] || {})), m = b, _querya("[data-lang]").forEach(function(a) {
        var b = o.get(a.dataset.lang);
        return a.dataset.tooltip ? a.dataset.tooltip = b : a.title ? a.title = ai(b) : void(a.textContent = b)
      }), ag.filter && (ag.filter.placeholder = o.get("filter"))
    }

    function b(a) {
      if (a) return "nb" === a || "nn" === a ? "no" : !!l.includes(a) && a
    }
    var h = n("lang", !0),
      c = b(h);
    "reset" === h && j.remove("files:lang:current"), c && j.set("files:lang:current", c);
    var _ = c || b(j.get("files:lang:current")) || function() {
      if (_c.lang_auto && e.nav_langs)
        for (var a = 0; a < e.nav_langs.length; a++) {
          var c = e.nav_langs[a].toLowerCase().split("-");
          if ("tw" === c[1]) return;
          var d = b(c[0]);
          if (d) return d
        }
    }() || b(_c.lang_default) || "en";
    return "en" === _ ? Object.assign(m, g.en || {}) : p(_), o
  }();
  ! function() {
    var b = "https://cdn.jsdelivr.net/npm/",
      a = {
        codemirror: "codemirror@5.65.2",
        headroom: "headroom.js@0.12.0",
        mousetrap: "mousetrap@1.6.5",
        uppy: "uppy@2.8.0",
        pannellum: "pannellum@2.5.6"
      };

    function c(a) {
      a.loading = !1, a.loaded = !0, i(a.complete, function(a) {
        a()
      }), delete a.complete, delete a.src
    }

    function d(a, c, d) {
      var e = 0;
      i(a, function(f) {
        ! function(c, f, d) {
          var e = "js" == d.type || "js" == c.slice(-2),
            a = document.createElement(e ? "script" : "link");
          a[e ? "src" : "href"] = c.startsWith("http") ? c : b + c, f && (a.onload = f), d.error && (a.onerror = d.error), e ? document.body.appendChild(a) : (a.type = "text/css", a.rel = "stylesheet", document.head.insertBefore(a, _tag("link", document.head)[0]))
        }(f, function() {
          ++e === a.length && c && c()
        }, d)
      })
    }
    u.plugins = {
      codemirror: {
        src: [
          [a.codemirror + "/lib/codemirror.min.js", a.codemirror + "/lib/codemirror.css"],
          [a.codemirror + "/mode/meta.js", a.codemirror + "/addon/mode/loadmode.js"]
        ],
        complete: [function() {
          CodeMirror.modeURL = b + a.codemirror + "/mode/%N/%N.js"
        }]
      },
      headroom: {
        src: [a.headroom + "/dist/headroom.min.js"]
      },
      mousetrap: {
        src: [a.mousetrap + "/mousetrap.min.js"]
      },
      pannellum: {
        src: [a.pannellum + "/build/pannellum.min.js"]
      },
      uppy: {
        src: [a.uppy + "/dist/uppy.min.js", a.uppy + "/dist/uppy.min.css"]
      }
    }, f.load_plugin = function(e, b, f) {
      u.plugins[e] || (u.plugins[e] = {});
      var a = f ? Object.assign(u.plugins[e], f) : u.plugins[e];
      if (a.loaded) b && b();
      else if (a.loading) b && a.complete.push(b);
      else {
        a.loading = !0, a.complete || (a.complete = []), b && a.complete.push(b);
        var g = a.src && Array.isArray(a.src[0]);
        d(g ? a.src[0] : a.src, function() {
          g ? d(a.src[1], function() {
            c(a)
          }, a) : c(a)
        }, a)
      }
    }, f.load_plugin("mousetrap", function() {
      Mousetrap.bind(["mod+f"], function(a) {
        a.preventDefault(), u.headroom.pin(), ag.filter.focus()
      })
    }), "scroll" === _c.topbar_sticky && getComputedStyle(ag.topbar).position.match("sticky") && f.load_plugin("headroom", function() {
      if (Headroom.cutsTheMustard) {
        var a = {
          tolerance: {
            down: 10,
            up: 20
          },
          offset: ag.topbar.clientHeight
        };
        u.headroom = new Headroom(ag.topbar, a), u.headroom.init()
      }
    })
  }();
  var ak = _c.menu_exists ? 2 : 1,
    al = !0;

  function am() {
    if (ak--) return !ak && setTimeout(am, 1e3);
    var d = t("ZmlsZXM6cXJ4"),
      a = (t("ZmlsZXM6cHVyY2hhc2Vk"), location.hostname),
      b = j.get(d);
    if (!b || b != _c.qrx && t(b) != a) {
      var c = _c.x3_path && !_c.qrx;
      return (_c.qrx || c || !a || a.includes(".")) && (!_c.qrx || "string" == typeof _c.qrx && /^[a-f0-9]{32}$/.test(_c.qrx)) ? void Y({
        params: (_c.qrx ? "key=" + _c.qrx + "&" : "") + (c ? "app=1&domain=" : "app=2&host=") + encodeURI(a),
        url: t("aHR0cHM6Ly9hdXRoLnBob3RvLmdhbGxlcnkv"),
        json_response: !0,
        complete: function(b, f, e) {
          if (console.log(b, f, e), e && b && b.hasOwnProperty("status")) return b.status && 301 != b.status ? void(c || j.set(d, _c.qrx || btoa(a))) : C(_c.qrx, b)
        }
      }) : null
    }
  }! function() {
    function d(a, b, c) {
      return a.format(b) + (c ? '<span class="relative-time">' + a.fromNow() + "</span>" : "")
    }

    function g(a) {
      dayjs.locale(a), ag.main.style.setProperty("--list-date-flex", dayjs().hour(22).date(22).format("L LT").length - 16), i(_tag("time"), function(a) {
        if (a.dataset.time) {
          var b = dayjs.unix(a.dataset.time);
          a.innerHTML = d(b, a.dataset.format, a.children[0]), a.dataset.titleFormat && (a.title = b.format(a.dataset.titleFormat) + " \xe2\u20AC\u201D " + b.fromNow())
        }
      }), _c.current_dir && (_c.current_dir.html = !1)
    }

    function c(a) {
      f.load_plugin("dayjs_locale_" + a, function() {
        g(a)
      }, {
        src: ["dayjs@1.11.0/locale/" + a + ".js"]
      })
    }
    f.get_time = function(b, c, f, g) {
      var a = dayjs.unix(b.mtime);
      return '<time datetime="' + a.format() + '" data-time="' + b.mtime + '" data-format="' + c + '"' + (f && e.is_pointer ? ' title="' + a.format("LLLL") + " ~ " + a.fromNow() + '" data-title-format="LLLL"' : "") + ">" + d(a, c, g) + "</time>"
    }, dayjs.extend(dayjs_plugin_localizedFormat), dayjs.extend(dayjs_plugin_relativeTime), f.dayjs_locale = function(b) {
      if ("en" === b) return g(b);
      (b = a(b)) && c(b)
    };
    var h = ["af", "am", "ar-dz", "ar-iq", "ar-kw", "ar-ly", "ar-ma", "ar-sa", "ar-tn", "ar", "az", "be", "bg", "bi", "bm", "bn", "bo", "br", "bs", "ca", "cs", "cv", "cy", "da", "de-at", "de-ch", "de", "dv", "el", "en-au", "en-ca", "en-gb", "en-ie", "en-il", "en-in", "en-nz", "en-sg", "en-tt", "en", "eo", "es-do", "es-mx", "et", "eu", "fa", "fi", "fo", "fr-ca", "fr-ch", "fr", "fy", "ga", "gd", "gl", "gom-latn", "gu", "he", "hi", "hr", "ht", "hu", "hy-am", "id", "es", "it-ch", "it", "ja", "jv", "ka", "kk", "km", "kn", "ko", "ku", "ky", "lb", "lo", "lt", "lv", "me", "mi", "mk", "ml", "mn", "mr", "ms-my", "ms", "mt", "my", "nb", "ne", "nl-be", "nl", "nn", "oc-lnc", "pa-in", "pl", "pt-br", "pt", "ro", "ru", "rw", "sd", "se", "is", "si", "sl", "sq", "sr-cyrl", "sr", "ss", "sv-fi", "sv", "sw", "ta", "te", "tet", "tg", "th", "tk", "tl-ph", "tlh", "tr", "tzl", "tzm-latn", "tzm", "ug-cn", "uk", "ur", "uz-latn", "uz", "vi", "x-pseudo", "yo", "zh-cn", "zh-hk", "zh-tw", "zh", "es-pr", "es-us", "sk"];

    function a(a) {
      if (a) return "no" === a || "nn" === a ? "nb" : !!h.includes(a) && a
    }
    var b = a(n("lang", !0)) || a(j.get("files:lang:current")) || function() {
      if (_c.lang_auto && e.nav_langs)
        for (var b = 0; b < e.nav_langs.length; b++) {
          var c = e.nav_langs[b].toLowerCase(),
            d = a(c) || !!c.includes("-") && a(c.split("-")[0]);
          if (d) return d
        }
    }() || a(_c.lang_default) || "en";
    ["en", "en-us"].includes(b) || c(b)
  }(),
  function() {
    var c = {
        bell: "M16,17H7V10.5C7,8 9,6 11.5,6C14,6 16,8 16,10.5M18,16V10.5C18,7.43 15.86,4.86 13,4.18V3.5A1.5,1.5 0 0,0 11.5,2A1.5,1.5 0 0,0 10,3.5V4.18C7.13,4.86 5,7.43 5,10.5V16L3,18V19H20V18M11.5,22A2,2 0 0,0 13.5,20H9.5A2,2 0 0,0 11.5,22Z",
        check: "M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z",
        close: "M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z",
        dots: "M12,16A2,2 0 0,1 14,18A2,2 0 0,1 12,20A2,2 0 0,1 10,18A2,2 0 0,1 12,16M12,10A2,2 0 0,1 14,12A2,2 0 0,1 12,14A2,2 0 0,1 10,12A2,2 0 0,1 12,10M12,4A2,2 0 0,1 14,6A2,2 0 0,1 12,8A2,2 0 0,1 10,6A2,2 0 0,1 12,4Z",
        expand: "M10,21V19H6.41L10.91,14.5L9.5,13.09L5,17.59V14H3V21H10M14.5,10.91L19,6.41V10H21V3H14V5H17.59L13.09,9.5L14.5,10.91Z",
        collapse: "M19.5,3.09L15,7.59V4H13V11H20V9H16.41L20.91,4.5L19.5,3.09M4,13V15H7.59L3.09,19.5L4.5,20.91L9,16.41V20H11V13H4Z",
        zoom_in: "M15.5,14L20.5,19L19,20.5L14,15.5V14.71L13.73,14.43C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.43,13.73L14.71,14H15.5M9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14M12,10H10V12H9V10H7V9H9V7H10V9H12V10Z",
        zoom_out: "M15.5,14H14.71L14.43,13.73C15.41,12.59 16,11.11 16,9.5A6.5,6.5 0 0,0 9.5,3A6.5,6.5 0 0,0 3,9.5A6.5,6.5 0 0,0 9.5,16C11.11,16 12.59,15.41 13.73,14.43L14,14.71V15.5L19,20.5L20.5,19L15.5,14M9.5,14C7,14 5,12 5,9.5C5,7 7,5 9.5,5C12,5 14,7 14,9.5C14,12 12,14 9.5,14M7,9H12V10H7V9Z",
        chevron_left: "M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z",
        chevron_right: "M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z",
        arrow_left: "M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z",
        arrow_right: "M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z",
        link: "M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z",
        logout: "M14.08,15.59L16.67,13H7V11H16.67L14.08,8.41L15.5,7L20.5,12L15.5,17L14.08,15.59M19,3A2,2 0 0,1 21,5V9.67L19,7.67V5H5V19H19V16.33L21,14.33V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5C3,3.89 3.89,3 5,3H19Z",
        download: "M5,20H19V18H5M19,9H15V3H9V9H5L12,16L19,9Z",
        tray_arrow_down: "M2 12H4V17H20V12H22V17C22 18.11 21.11 19 20 19H4C2.9 19 2 18.11 2 17V12M12 15L17.55 9.54L16.13 8.13L13 11.25V2H11V11.25L7.88 8.13L6.46 9.55L12 15Z",
        tray_arrow_up: "M2 12H4V17H20V12H22V17C22 18.11 21.11 19 20 19H4C2.9 19 2 18.11 2 17V12M12 2L6.46 7.46L7.88 8.88L11 5.75V15H13V5.75L16.13 8.88L17.55 7.45L12 2Z",
        content_copy: "M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z",
        pencil_outline: "M14.06,9L15,9.94L5.92,19H5V18.08L14.06,9M17.66,3C17.41,3 17.15,3.1 16.96,3.29L15.13,5.12L18.88,8.87L20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18.17,3.09 17.92,3 17.66,3M14.06,6.19L3,17.25V21H6.75L17.81,9.94L14.06,6.19Z",
        close_thick: "M20 6.91L17.09 4L12 9.09L6.91 4L4 6.91L9.09 12L4 17.09L6.91 20L12 14.91L17.09 20L20 17.09L14.91 12L20 6.91Z",
        plus_circle_multiple_outline: "M16,8H14V11H11V13H14V16H16V13H19V11H16M2,12C2,9.21 3.64,6.8 6,5.68V3.5C2.5,4.76 0,8.09 0,12C0,15.91 2.5,19.24 6,20.5V18.32C3.64,17.2 2,14.79 2,12M15,3C10.04,3 6,7.04 6,12C6,16.96 10.04,21 15,21C19.96,21 24,16.96 24,12C24,7.04 19.96,3 15,3M15,19C11.14,19 8,15.86 8,12C8,8.14 11.14,5 15,5C18.86,5 22,8.14 22,12C22,15.86 18.86,19 15,19Z",
        upload: "M9,16V10H5L12,3L19,10H15V16H9M5,20V18H19V20H5Z",
        clipboard: "M19,3H14.82C14.4,1.84 13.3,1 12,1C10.7,1 9.6,1.84 9.18,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5A2,2 0 0,0 19,3M12,3A1,1 0 0,1 13,4A1,1 0 0,1 12,5A1,1 0 0,1 11,4A1,1 0 0,1 12,3M7,7H17V5H19V19H5V5H7V7M7.5,13.5L9,12L11,14L15.5,9.5L17,11L11,17L7.5,13.5Z",
        save_edit: "M10,19L10.14,18.86C8.9,18.5 8,17.36 8,16A3,3 0 0,1 11,13C12.36,13 13.5,13.9 13.86,15.14L20,9V7L16,3H4C2.89,3 2,3.9 2,5V19A2,2 0 0,0 4,21H10V19M4,5H14V9H4V5M20.04,12.13C19.9,12.13 19.76,12.19 19.65,12.3L18.65,13.3L20.7,15.35L21.7,14.35C21.92,14.14 21.92,13.79 21.7,13.58L20.42,12.3C20.31,12.19 20.18,12.13 20.04,12.13M18.07,13.88L12,19.94V22H14.06L20.12,15.93L18.07,13.88Z",
        marker: "M18.27 6C19.28 8.17 19.05 10.73 17.94 12.81C17 14.5 15.65 15.93 14.5 17.5C14 18.2 13.5 18.95 13.13 19.76C13 20.03 12.91 20.31 12.81 20.59C12.71 20.87 12.62 21.15 12.53 21.43C12.44 21.69 12.33 22 12 22H12C11.61 22 11.5 21.56 11.42 21.26C11.18 20.53 10.94 19.83 10.57 19.16C10.15 18.37 9.62 17.64 9.08 16.93L18.27 6M9.12 8.42L5.82 12.34C6.43 13.63 7.34 14.73 8.21 15.83C8.42 16.08 8.63 16.34 8.83 16.61L13 11.67L12.96 11.68C11.5 12.18 9.88 11.44 9.3 10C9.22 9.83 9.16 9.63 9.12 9.43C9.07 9.06 9.06 8.79 9.12 8.43L9.12 8.42M6.58 4.62L6.57 4.63C4.95 6.68 4.67 9.53 5.64 11.94L9.63 7.2L9.58 7.15L6.58 4.62M14.22 2.36L11 6.17L11.04 6.16C12.38 5.7 13.88 6.28 14.56 7.5C14.71 7.78 14.83 8.08 14.87 8.38C14.93 8.76 14.95 9.03 14.88 9.4L14.88 9.41L18.08 5.61C17.24 4.09 15.87 2.93 14.23 2.37L14.22 2.36M9.89 6.89L13.8 2.24L13.76 2.23C13.18 2.08 12.59 2 12 2C10.03 2 8.17 2.85 6.85 4.31L6.83 4.32L9.89 6.89Z",
        info: "M11,9H13V7H11M12,20C7.59,20 4,16.41 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,16.41 16.41,20 12,20M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M11,17H13V11H11V17Z",
        folder: "M4 5v14h16V7h-8.414l-2-2H4zm8.414 0H21a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h7.414l2 2z",
        folder_plus: "M13 9h-2v3H8v2h3v3h2v-3h3v-2h-3z",
        folder_minus: "M7.874 12h8v2h-8z",
        folder_forbid: "M22 11.255a6.972 6.972 0 0 0-2-.965V7h-8.414l-2-2H4v14h7.29a6.96 6.96 0 0 0 .965 2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h7.414l2 2H21a1 1 0 0 1 1 1v5.255zM18 22a5 5 0 1 1 0-10a5 5 0 0 1 0 10zm-1.293-2.292a3 3 0 0 0 4.001-4.001l-4.001 4zm-1.415-1.415l4.001-4a3 3 0 0 0-4.001 4.001z",
        folder_link: "M22 13h-2V7h-8.414l-2-2H4v14h9v2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h7.414l2 2H21a1 1 0 0 1 1 1v7zm-4 4v-3.5l5 4.5l-5 4.5V19h-3v-2h3z",
        folder_wrench: "M13.03 20H4C2.9 20 2 19.11 2 18V6C2 4.89 2.89 4 4 4H10L12 6H20C21.1 6 22 6.89 22 8V17.5L20.96 16.44C20.97 16.3 21 16.15 21 16C21 13.24 18.76 11 16 11S11 13.24 11 16C11 17.64 11.8 19.09 13.03 20M22.87 21.19L18.76 17.08C19.17 16.04 18.94 14.82 18.08 13.97C17.18 13.06 15.83 12.88 14.74 13.38L16.68 15.32L15.33 16.68L13.34 14.73C12.8 15.82 13.05 17.17 13.93 18.08C14.79 18.94 16 19.16 17.05 18.76L21.16 22.86C21.34 23.05 21.61 23.05 21.79 22.86L22.83 21.83C23.05 21.65 23.05 21.33 22.87 21.19Z",
        folder_cog_outline: "M4 4C2.89 4 2 4.89 2 6V18C2 19.11 2.9 20 4 20H12V18H4V8H20V12H22V8C22 6.89 21.1 6 20 6H12L10 4M18 14C17.87 14 17.76 14.09 17.74 14.21L17.55 15.53C17.25 15.66 16.96 15.82 16.7 16L15.46 15.5C15.35 15.5 15.22 15.5 15.15 15.63L14.15 17.36C14.09 17.47 14.11 17.6 14.21 17.68L15.27 18.5C15.25 18.67 15.24 18.83 15.24 19C15.24 19.17 15.25 19.33 15.27 19.5L14.21 20.32C14.12 20.4 14.09 20.53 14.15 20.64L15.15 22.37C15.21 22.5 15.34 22.5 15.46 22.5L16.7 22C16.96 22.18 17.24 22.35 17.55 22.47L17.74 23.79C17.76 23.91 17.86 24 18 24H20C20.11 24 20.22 23.91 20.24 23.79L20.43 22.47C20.73 22.34 21 22.18 21.27 22L22.5 22.5C22.63 22.5 22.76 22.5 22.83 22.37L23.83 20.64C23.89 20.53 23.86 20.4 23.77 20.32L22.7 19.5C22.72 19.33 22.74 19.17 22.74 19C22.74 18.83 22.73 18.67 22.7 18.5L23.76 17.68C23.85 17.6 23.88 17.47 23.82 17.36L22.82 15.63C22.76 15.5 22.63 15.5 22.5 15.5L21.27 16C21 15.82 20.73 15.65 20.42 15.53L20.23 14.21C20.22 14.09 20.11 14 20 14M19 17.5C19.83 17.5 20.5 18.17 20.5 19C20.5 19.83 19.83 20.5 19 20.5C18.16 20.5 17.5 19.83 17.5 19C17.5 18.17 18.17 17.5 19 17.5Z",
        folder_open: "M12.414 5H21a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h7.414l2 2zM4 5v14h16V7h-8.414l-2-2H4zm8 7V9l4 4l-4 4v-3H8v-2h4z",
        folder_move_outline: "M20 18H4V8H20V18M12 6L10 4H4C2.9 4 2 4.89 2 6V18C2 19.11 2.9 20 4 20H20C21.11 20 22 19.11 22 18V8C22 6.9 21.11 6 20 6H12M11 14V12H15V9L19 13L15 17V14H11Z",
        alert_circle_outline: "M11,15H13V17H11V15M11,7H13V13H11V7M12,2C6.47,2 2,6.5 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20Z",
        date: "M12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22C6.47,22 2,17.5 2,12A10,10 0 0,1 12,2M12.5,7V12.25L17,14.92L16.25,16.15L11,13V7H12.5Z",
        camera: "M20,4H16.83L15,2H9L7.17,4H4A2,2 0 0,0 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6A2,2 0 0,0 20,4M20,18H4V6H8.05L9.88,4H14.12L15.95,6H20V18M12,7A5,5 0 0,0 7,12A5,5 0 0,0 12,17A5,5 0 0,0 17,12A5,5 0 0,0 12,7M12,15A3,3 0 0,1 9,12A3,3 0 0,1 12,9A3,3 0 0,1 15,12A3,3 0 0,1 12,15Z",
        cellphone: "M17,19H7V5H17M17,1H7C5.89,1 5,1.89 5,3V21A2,2 0 0,0 7,23H17A2,2 0 0,0 19,21V3C19,1.89 18.1,1 17,1Z",
        plus: "M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z",
        minus: "M19,13H5V11H19V13Z",
        menu: "M3,6H21V8H3V6M3,11H21V13H3V11M3,16H21V18H3V16Z",
        menu_back: "M5,13L9,17L7.6,18.42L1.18,12L7.6,5.58L9,7L5,11H21V13H5M21,6V8H11V6H21M21,16V18H11V16H21Z",
        gif: "M11.5 9H13v6h-1.5zM9 9H6c-.6 0-1 .5-1 1v4c0 .5.4 1 1 1h3c.6 0 1-.5 1-1v-2H8.5v1.5h-2v-3H10V10c0-.5-.4-1-1-1zM19 10.5V9h-4.5v6H16v-2h2v-1.5h-2v-1z",
        rotate_right: "M16.89,15.5L18.31,16.89C19.21,15.73 19.76,14.39 19.93,13H17.91C17.77,13.87 17.43,14.72 16.89,15.5M13,17.9V19.92C14.39,19.75 15.74,19.21 16.9,18.31L15.46,16.87C14.71,17.41 13.87,17.76 13,17.9M19.93,11C19.76,9.61 19.21,8.27 18.31,7.11L16.89,8.53C17.43,9.28 17.77,10.13 17.91,11M15.55,5.55L11,1V4.07C7.06,4.56 4,7.92 4,12C4,16.08 7.05,19.44 11,19.93V17.91C8.16,17.43 6,14.97 6,12C6,9.03 8.16,6.57 11,6.09V10L15.55,5.55Z",
        motion_play_outline: "M10 16.5L16 12L10 7.5M22 12C22 6.46 17.54 2 12 2C10.83 2 9.7 2.19 8.62 2.56L9.32 4.5C10.17 4.16 11.06 3.97 12 3.97C16.41 3.97 20.03 7.59 20.03 12C20.03 16.41 16.41 20.03 12 20.03C7.59 20.03 3.97 16.41 3.97 12C3.97 11.06 4.16 10.12 4.5 9.28L2.56 8.62C2.19 9.7 2 10.83 2 12C2 17.54 6.46 22 12 22C17.54 22 22 17.54 22 12M5.47 3.97C6.32 3.97 7 4.68 7 5.47C7 6.32 6.32 7 5.47 7C4.68 7 3.97 6.32 3.97 5.47C3.97 4.68 4.68 3.97 5.47 3.97Z",
        motion_pause_outline: "M22 12C22 6.46 17.54 2 12 2C10.83 2 9.7 2.19 8.62 2.56L9.32 4.5C10.17 4.16 11.06 3.97 12 3.97C16.41 3.97 20.03 7.59 20.03 12C20.03 16.41 16.41 20.03 12 20.03C7.59 20.03 3.97 16.41 3.97 12C3.97 11.06 4.16 10.12 4.5 9.28L2.56 8.62C2.19 9.7 2 10.83 2 12C2 17.54 6.46 22 12 22C17.54 22 22 17.54 22 12M5.47 7C4.68 7 3.97 6.32 3.97 5.47C3.97 4.68 4.68 3.97 5.47 3.97C6.32 3.97 7 4.68 7 5.47C7 6.32 6.32 7 5.47 7M9 9H11V15H9M13 9H15V15H13",
        panorama_variant: "M20.7 4.1C18.7 4.8 15.9 5.5 12 5.5C8.1 5.5 5.1 4.7 3.3 4.1C2.7 3.8 2 4.3 2 5V19C2 19.7 2.7 20.2 3.3 20C5.4 19.3 8.1 18.5 12 18.5C15.9 18.5 18.7 19.3 20.7 20C21.4 20.2 22 19.7 22 19V5C22 4.3 21.3 3.8 20.7 4.1M12 15C9.7 15 7.5 15.1 5.5 15.4L9.2 11L11.2 13.4L14 10L18.5 15.4C16.5 15.1 14.3 15 12 15Z",
        sort_name_asc: "M9.25 5L12.5 1.75L15.75 5H9.25M8.89 14.3H6L5.28 17H2.91L6 7H9L12.13 17H9.67L8.89 14.3M6.33 12.68H8.56L7.93 10.56L7.67 9.59L7.42 8.63H7.39L7.17 9.6L6.93 10.58L6.33 12.68M13.05 17V15.74L17.8 8.97V8.91H13.5V7H20.73V8.34L16.09 15V15.08H20.8V17H13.05Z",
        sort_name_desc: "M15.75 19L12.5 22.25L9.25 19H15.75M8.89 14.3H6L5.28 17H2.91L6 7H9L12.13 17H9.67L8.89 14.3M6.33 12.68H8.56L7.93 10.56L7.67 9.59L7.42 8.63H7.39L7.17 9.6L6.93 10.58L6.33 12.68M13.05 17V15.74L17.8 8.97V8.91H13.5V7H20.73V8.34L16.09 15V15.08H20.8V17H13.05Z",
        sort_kind_asc: "M3 11H15V13H3M3 18V16H21V18M3 6H9V8H3Z",
        sort_kind_desc: "M3,13H15V11H3M3,6V8H21V6M3,18H9V16H3V18Z",
        sort_size_asc: "M10,13V11H18V13H10M10,19V17H14V19H10M10,7V5H22V7H10M6,17H8.5L5,20.5L1.5,17H4V7H1.5L5,3.5L8.5,7H6V17Z",
        sort_size_desc: "M10,13V11H18V13H10M10,19V17H14V19H10M10,7V5H22V7H10M6,17H8.5L5,20.5L1.5,17H4V7H1.5L5,3.5L8.5,7H6V17Z",
        sort_date_asc: "M7.78 7C9.08 7.04 10 7.53 10.57 8.46C11.13 9.4 11.41 10.56 11.39 11.95C11.4 13.5 11.09 14.73 10.5 15.62C9.88 16.5 8.95 16.97 7.71 17C6.45 16.96 5.54 16.5 4.96 15.56C4.38 14.63 4.09 13.45 4.09 12S4.39 9.36 5 8.44C5.59 7.5 6.5 7.04 7.78 7M7.75 8.63C7.31 8.63 6.96 8.9 6.7 9.46C6.44 10 6.32 10.87 6.32 12C6.31 13.15 6.44 14 6.69 14.54C6.95 15.1 7.31 15.37 7.77 15.37C8.69 15.37 9.16 14.24 9.17 12C9.17 9.77 8.7 8.65 7.75 8.63M13.33 17V15.22L13.76 15.24L14.3 15.22L15.34 15.03C15.68 14.92 16 14.78 16.26 14.58C16.59 14.35 16.86 14.08 17.07 13.76C17.29 13.45 17.44 13.12 17.53 12.78L17.5 12.77C17.05 13.19 16.38 13.4 15.47 13.41C14.62 13.4 13.91 13.15 13.34 12.65S12.5 11.43 12.46 10.5C12.47 9.5 12.81 8.69 13.47 8.03C14.14 7.37 15 7.03 16.12 7C17.37 7.04 18.29 7.45 18.88 8.24C19.47 9 19.76 10 19.76 11.19C19.75 12.15 19.61 13 19.32 13.76C19.03 14.5 18.64 15.13 18.12 15.64C17.66 16.06 17.11 16.38 16.47 16.61C15.83 16.83 15.12 16.96 14.34 17H13.33M16.06 8.63C15.65 8.64 15.32 8.8 15.06 9.11C14.81 9.42 14.68 9.84 14.68 10.36C14.68 10.8 14.8 11.16 15.03 11.46C15.27 11.77 15.63 11.92 16.11 11.93C16.43 11.93 16.7 11.86 16.92 11.74C17.14 11.61 17.3 11.46 17.41 11.28C17.5 11.17 17.53 10.97 17.53 10.71C17.54 10.16 17.43 9.69 17.2 9.28C16.97 8.87 16.59 8.65 16.06 8.63M9.25 5L12.5 1.75L15.75 5H9.25",
        sort_date_desc: "M7.78 7C9.08 7.04 10 7.53 10.57 8.46C11.13 9.4 11.41 10.56 11.39 11.95C11.4 13.5 11.09 14.73 10.5 15.62C9.88 16.5 8.95 16.97 7.71 17C6.45 16.96 5.54 16.5 4.96 15.56C4.38 14.63 4.09 13.45 4.09 12S4.39 9.36 5 8.44C5.59 7.5 6.5 7.04 7.78 7M7.75 8.63C7.31 8.63 6.96 8.9 6.7 9.46C6.44 10 6.32 10.87 6.32 12C6.31 13.15 6.44 14 6.69 14.54C6.95 15.1 7.31 15.37 7.77 15.37C8.69 15.37 9.16 14.24 9.17 12C9.17 9.77 8.7 8.65 7.75 8.63M13.33 17V15.22L13.76 15.24L14.3 15.22L15.34 15.03C15.68 14.92 16 14.78 16.26 14.58C16.59 14.35 16.86 14.08 17.07 13.76C17.29 13.45 17.44 13.12 17.53 12.78L17.5 12.77C17.05 13.19 16.38 13.4 15.47 13.41C14.62 13.4 13.91 13.15 13.34 12.65S12.5 11.43 12.46 10.5C12.47 9.5 12.81 8.69 13.47 8.03C14.14 7.37 15 7.03 16.12 7C17.37 7.04 18.29 7.45 18.88 8.24C19.47 9 19.76 10 19.76 11.19C19.75 12.15 19.61 13 19.32 13.76C19.03 14.5 18.64 15.13 18.12 15.64C17.66 16.06 17.11 16.38 16.47 16.61C15.83 16.83 15.12 16.96 14.34 17H13.33M16.06 8.63C15.65 8.64 15.32 8.8 15.06 9.11C14.81 9.42 14.68 9.84 14.68 10.36C14.68 10.8 14.8 11.16 15.03 11.46C15.27 11.77 15.63 11.92 16.11 11.93C16.43 11.93 16.7 11.86 16.92 11.74C17.14 11.61 17.3 11.46 17.41 11.28C17.5 11.17 17.53 10.97 17.53 10.71C17.54 10.16 17.43 9.69 17.2 9.28C16.97 8.87 16.59 8.65 16.06 8.63M15.75 19L12.5 22.25L9.25 19H15.75Z",
        filesize: "M3,13H15V11H3M3,6V8H21V6M3,18H9V16H3V18Z",
        layout_list: "M7,5H21V7H7V5M7,13V11H21V13H7M4,4.5A1.5,1.5 0 0,1 5.5,6A1.5,1.5 0 0,1 4,7.5A1.5,1.5 0 0,1 2.5,6A1.5,1.5 0 0,1 4,4.5M4,10.5A1.5,1.5 0 0,1 5.5,12A1.5,1.5 0 0,1 4,13.5A1.5,1.5 0 0,1 2.5,12A1.5,1.5 0 0,1 4,10.5M7,19V17H21V19H7M4,16.5A1.5,1.5 0 0,1 5.5,18A1.5,1.5 0 0,1 4,19.5A1.5,1.5 0 0,1 2.5,18A1.5,1.5 0 0,1 4,16.5Z",
        layout_imagelist: "M3,4H7V8H3V4M9,5V7H21V5H9M3,10H7V14H3V10M9,11V13H21V11H9M3,16H7V20H3V16M9,17V19H21V17H9",
        layout_blocks: "M2 14H8V20H2M16 8H10V10H16M2 10H8V4H2M10 4V6H22V4M10 20H16V18H10M10 16H22V14H10",
        layout_grid: "M3,9H7V5H3V9M3,14H7V10H3V14M8,14H12V10H8V14M13,14H17V10H13V14M8,9H12V5H8V9M13,5V9H17V5H13M18,14H22V10H18V14M3,19H7V15H3V19M8,19H12V15H8V19M13,19H17V15H13V19M18,19H22V15H18V19M18,5V9H22V5H18Z",
        layout_rows: "M3,19H9V12H3V19M10,19H22V12H10V19M3,5V11H22V5H3Z",
        layout_columns: "M2,5V19H8V5H2M9,5V10H15V5H9M16,5V14H22V5H16M9,11V19H15V11H9M16,15V19H22V15H16Z",
        lock_outline: "M12,17C10.89,17 10,16.1 10,15C10,13.89 10.89,13 12,13A2,2 0 0,1 14,15A2,2 0 0,1 12,17M18,20V10H6V20H18M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6C4.89,22 4,21.1 4,20V10C4,8.89 4.89,8 6,8H7V6A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,3A3,3 0 0,0 9,6V8H15V6A3,3 0 0,0 12,3Z",
        lock_open_outline: "M18,20V10H6V20H18M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6C4.89,22 4,21.1 4,20V10A2,2 0 0,1 6,8H15V6A3,3 0 0,0 12,3A3,3 0 0,0 9,6H7A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,17A2,2 0 0,1 10,15A2,2 0 0,1 12,13A2,2 0 0,1 14,15A2,2 0 0,1 12,17Z",
        open_in_new: "M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z",
        play: "M8,5.14V19.14L19,12.14L8,5.14Z",
        pause: "M14,19H18V5H14M6,19H10V5H6V19Z",
        menu_down: "M7,13L12,18L17,13H7Z",
        menu_up: "M7,12L12,7L17,12H7Z",
        home: "M20 6H12L10 4H4A2 2 0 0 0 2 6V18A2 2 0 0 0 4 20H20A2 2 0 0 0 22 18V8A2 2 0 0 0 20 6M17 13V17H15V14H13V17H11V13H9L14 9L19 13Z",
        image_search_outline: "M15.5,9C16.2,9 16.79,8.76 17.27,8.27C17.76,7.79 18,7.2 18,6.5C18,5.83 17.76,5.23 17.27,4.73C16.79,4.23 16.2,4 15.5,4C14.83,4 14.23,4.23 13.73,4.73C13.23,5.23 13,5.83 13,6.5C13,7.2 13.23,7.79 13.73,8.27C14.23,8.76 14.83,9 15.5,9M19.31,8.91L22.41,12L21,13.41L17.86,10.31C17.08,10.78 16.28,11 15.47,11C14.22,11 13.16,10.58 12.3,9.7C11.45,8.83 11,7.77 11,6.5C11,5.27 11.45,4.2 12.33,3.33C13.2,2.45 14.27,2 15.5,2C16.77,2 17.83,2.45 18.7,3.33C19.58,4.2 20,5.27 20,6.5C20,7.33 19.78,8.13 19.31,8.91M16.5,18H5.5L8.25,14.5L10.22,16.83L12.94,13.31L16.5,18M18,13L20,15V20C20,20.55 19.81,21 19.41,21.4C19,21.79 18.53,22 18,22H4C3.45,22 3,21.79 2.6,21.4C2.21,21 2,20.55 2,20V6C2,5.47 2.21,5 2.6,4.59C3,4.19 3.45,4 4,4H9.5C9.2,4.64 9.03,5.31 9,6H4V20H18V13Z",
        search: "M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z",
        file_default: "M14,10H19.5L14,4.5V10M5,3H15L21,9V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5C3,3.89 3.89,3 5,3M5,5V19H19V12H12V5H5Z",
        application: "M19,4C20.11,4 21,4.9 21,6V18A2,2 0 0,1 19,20H5C3.89,20 3,19.1 3,18V6A2,2 0 0,1 5,4H19M19,18V8H5V18H19Z",
        archive: "M14,17H12V15H10V13H12V15H14M14,9H12V11H14V13H12V11H10V9H12V7H10V5H12V7H14M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3Z",
        audio: "M14,3.23V5.29C16.89,6.15 19,8.83 19,12C19,15.17 16.89,17.84 14,18.7V20.77C18,19.86 21,16.28 21,12C21,7.72 18,4.14 14,3.23M16.5,12C16.5,10.23 15.5,8.71 14,7.97V16C15.5,15.29 16.5,13.76 16.5,12M3,9V15H7L12,20V4L7,9H3Z",
        cd: "M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,9A3,3 0 0,1 15,12A3,3 0 0,1 12,15A3,3 0 0,1 9,12A3,3 0 0,1 12,9Z",
        code: "M14.6,16.6L19.2,12L14.6,7.4L16,6L22,12L16,18L14.6,16.6M9.4,16.6L4.8,12L9.4,7.4L8,6L2,12L8,18L9.4,16.6Z",
        excel: "M16.2,17H14.2L12,13.2L9.8,17H7.8L11,12L7.8,7H9.8L12,10.8L14.2,7H16.2L13,12M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3Z",
        font: "M17,8H20V20H21V21H17V20H18V17H14L12.5,20H14V21H10V20H11L17,8M18,9L14.5,16H18V9M5,3H10C11.11,3 12,3.89 12,5V16H9V11H6V16H3V5C3,3.89 3.89,3 5,3M6,5V9H9V5H6Z",
        image: "M8.5,13.5L11,16.5L14.5,12L19,18H5M21,19V5C21,3.89 20.1,3 19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19Z",
        pdf: "M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5C3,3.89 3.89,3 5,3H19M10.59,10.08C10.57,10.13 10.3,11.84 8.5,14.77C8.5,14.77 5,16.58 5.83,17.94C6.5,19 8.15,17.9 9.56,15.27C9.56,15.27 11.38,14.63 13.79,14.45C13.79,14.45 17.65,16.19 18.17,14.34C18.69,12.5 15.12,12.9 14.5,13.09C14.5,13.09 12.46,11.75 12,9.89C12,9.89 13.13,5.95 11.38,6C9.63,6.05 10.29,9.12 10.59,10.08M11.4,11.13C11.43,11.13 11.87,12.33 13.29,13.58C13.29,13.58 10.96,14.04 9.9,14.5C9.9,14.5 10.9,12.75 11.4,11.13M15.32,13.84C15.9,13.69 17.64,14 17.58,14.32C17.5,14.65 15.32,13.84 15.32,13.84M8.26,15.7C7.73,16.91 6.83,17.68 6.6,17.67C6.37,17.66 7.3,16.07 8.26,15.7M11.4,8.76C11.39,8.71 11.03,6.57 11.4,6.61C11.94,6.67 11.4,8.71 11.4,8.76Z",
        powerpoint: "M9.8,13.4H12.3C13.8,13.4 14.46,13.12 15.1,12.58C15.74,12.03 16,11.25 16,10.23C16,9.26 15.75,8.5 15.1,7.88C14.45,7.29 13.83,7 12.3,7H8V17H9.8V13.4M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5A2,2 0 0,1 3,19V5C3,3.89 3.9,3 5,3H19M9.8,12V8.4H12.1C12.76,8.4 13.27,8.65 13.6,9C13.93,9.35 14.1,9.72 14.1,10.24C14.1,10.8 13.92,11.19 13.6,11.5C13.28,11.81 12.9,12 12.22,12H9.8Z",
        text: "M14,17H7V15H14M17,13H7V11H17M17,9H7V7H17M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3Z",
        video: "M17,10.5V7A1,1 0 0,0 16,6H4A1,1 0 0,0 3,7V17A1,1 0 0,0 4,18H16A1,1 0 0,0 17,17V13.5L21,17.5V6.5L17,10.5Z",
        word: "M15.5,17H14L12,9.5L10,17H8.5L6.1,7H7.8L9.34,14.5L11.3,7H12.7L14.67,14.5L16.2,7H17.9M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3Z",
        translate: "M12.87,15.07L10.33,12.56L10.36,12.53C12.1,10.59 13.34,8.36 14.07,6H17V4H10V2H8V4H1V6H12.17C11.5,7.92 10.44,9.75 9,11.35C8.07,10.32 7.3,9.19 6.69,8H4.69C5.42,9.63 6.42,11.17 7.67,12.56L2.58,17.58L4,19L9,14L12.11,17.11L12.87,15.07M18.5,10H16.5L12,22H14L15.12,19H19.87L21,22H23L18.5,10M15.88,17L17.5,12.67L19.12,17H15.88Z",
        web: "M16.36,14C16.44,13.34 16.5,12.68 16.5,12C16.5,11.32 16.44,10.66 16.36,10H19.74C19.9,10.64 20,11.31 20,12C20,12.69 19.9,13.36 19.74,14M14.59,19.56C15.19,18.45 15.65,17.25 15.97,16H18.92C17.96,17.65 16.43,18.93 14.59,19.56M14.34,14H9.66C9.56,13.34 9.5,12.68 9.5,12C9.5,11.32 9.56,10.65 9.66,10H14.34C14.43,10.65 14.5,11.32 14.5,12C14.5,12.68 14.43,13.34 14.34,14M12,19.96C11.17,18.76 10.5,17.43 10.09,16H13.91C13.5,17.43 12.83,18.76 12,19.96M8,8H5.08C6.03,6.34 7.57,5.06 9.4,4.44C8.8,5.55 8.35,6.75 8,8M5.08,16H8C8.35,17.25 8.8,18.45 9.4,19.56C7.57,18.93 6.03,17.65 5.08,16M4.26,14C4.1,13.36 4,12.69 4,12C4,11.31 4.1,10.64 4.26,10H7.64C7.56,10.66 7.5,11.32 7.5,12C7.5,12.68 7.56,13.34 7.64,14M12,4.03C12.83,5.23 13.5,6.57 13.91,8H10.09C10.5,6.57 11.17,5.23 12,4.03M18.92,8H15.97C15.65,6.75 15.19,5.55 14.59,4.44C16.43,5.07 17.96,6.34 18.92,8M12,2C6.47,2 2,6.5 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z"
      },
      a = {
        application: '<path d="M35 14C36.11 14 37 14.9 37 16V28A2 2 0 0 1 35 30H21C19.89 30 19 29.1 19 28V16A2 2 0 0 1 21 14H35M35 28V18H21V28H35z"/>',
        archive: '<path d="M28.5,24v-2h2v-2h-2v-2h2v-2h-2v-2h2v-2h-2v-2h2V8h-2V6h-2v2h-2v2h2v2h-2v2h2v2h-2v2h2v2h-2v2h2v2 h-4v5c0,2.757,2.243,5,5,5s5-2.243,5-5v-5H28.5z M30.5,29c0,1.654-1.346,3-3,3s-3-1.346-3-3v-3h6V29z"/><path d="M26.5,30h2c0.552,0,1-0.447,1-1s-0.448-1-1-1h-2c-0.552,0-1,0.447-1,1S25.948,30,26.5,30z"/></g>',
        audio: '<path d="M35.67,14.986c-0.567-0.796-1.3-1.543-2.308-2.351c-3.914-3.131-4.757-6.277-4.862-6.738V5 c0-0.553-0.447-1-1-1s-1,0.447-1,1v1v8.359v9.053h-3.706c-3.882,0-6.294,1.961-6.294,5.117c0,3.466,2.24,5.706,5.706,5.706 c3.471,0,6.294-2.823,6.294-6.294V16.468l0.298,0.243c0.34,0.336,0.861,0.72,1.521,1.205c2.318,1.709,6.2,4.567,5.224,7.793 C35.514,25.807,35.5,25.904,35.5,26c0,0.43,0.278,0.826,0.71,0.957C36.307,26.986,36.404,27,36.5,27c0.43,0,0.826-0.278,0.957-0.71 C39.084,20.915,37.035,16.9,35.67,14.986z M26.5,27.941c0,2.368-1.926,4.294-4.294,4.294c-2.355,0-3.706-1.351-3.706-3.706 c0-2.576,2.335-3.117,4.294-3.117H26.5V27.941z M31.505,16.308c-0.571-0.422-1.065-0.785-1.371-1.081l-1.634-1.34v-3.473 c0.827,1.174,1.987,2.483,3.612,3.783c0.858,0.688,1.472,1.308,1.929,1.95c0.716,1.003,1.431,2.339,1.788,3.978 C34.502,18.515,32.745,17.221,31.505,16.308z"/>',
        cd: '<circle cx="27.5" cy="21" r="12"/><circle style="fill:#e9e9e0" cx="27.5" cy="21" r="3"/><path style="fill:#d3ccc9" d="M25.379,18.879c0.132-0.132,0.276-0.245,0.425-0.347l-2.361-8.813 c-1.615,0.579-3.134,1.503-4.427,2.796c-1.294,1.293-2.217,2.812-2.796,4.427l8.813,2.361 C25.134,19.155,25.247,19.011,25.379,18.879z"/><path style="fill:#d3ccc9" d="M30.071,23.486l2.273,8.483c1.32-0.582,2.56-1.402,3.641-2.484c1.253-1.253,2.16-2.717,2.743-4.275 l-8.188-2.194C30.255,22.939,29.994,23.2,30.071,23.486z"/>',
        code: '<path d="M15.5,24c-0.256,0-0.512-0.098-0.707-0.293c-0.391-0.391-0.391-1.023,0-1.414l6-6 c0.391-0.391,1.023-0.391,1.414,0s0.391,1.023,0,1.414l-6,6C16.012,23.902,15.756,24,15.5,24z"/><path d="M21.5,30c-0.256,0-0.512-0.098-0.707-0.293l-6-6c-0.391-0.391-0.391-1.023,0-1.414 s1.023-0.391,1.414,0l6,6c0.391,0.391,0.391,1.023,0,1.414C22.012,29.902,21.756,30,21.5,30z"/><path d="M33.5,30c-0.256,0-0.512-0.098-0.707-0.293c-0.391-0.391-0.391-1.023,0-1.414l6-6 c0.391-0.391,1.023-0.391,1.414,0s0.391,1.023,0,1.414l-6,6C34.012,29.902,33.756,30,33.5,30z"/><path d="M39.5,24c-0.256,0-0.512-0.098-0.707-0.293l-6-6c-0.391-0.391-0.391-1.023,0-1.414 s1.023-0.391,1.414,0l6,6c0.391,0.391,0.391,1.023,0,1.414C40.012,23.902,39.756,24,39.5,24z"/><path d="M24.5,32c-0.11,0-0.223-0.019-0.333-0.058c-0.521-0.184-0.794-0.755-0.61-1.276l6-17 c0.185-0.521,0.753-0.795,1.276-0.61c0.521,0.184,0.794,0.755,0.61,1.276l-6,17C25.298,31.744,24.912,32,24.5,32z"/>',
        font: '<path d="M33 18H36V30H37V31H33V30H34V27H30L28.5 30H30V31H26V30H27L33 18M34 19L30.5 26H34V19M21 13H26C27.11 13 28 13.89 28 15V26H25V21H22V26H19V15C19 13.89 19.89 13 21 13M22 15V19H25V15H22z"/>',
        excel: '<path style="fill:#c8bdb8" d="M23.5,16v-4h-12v4v2v2v2v2v2v2v2v4h10h2h21v-4v-2v-2v-2v-2v-2v-4H23.5z M13.5,14h8v2h-8V14z M13.5,18h8v2h-8V18z M13.5,22h8v2h-8V22z M13.5,26h8v2h-8V26z M21.5,32h-8v-2h8V32z M42.5,32h-19v-2h19V32z M42.5,28h-19v-2h19V28 z M42.5,24h-19v-2h19V24z M23.5,20v-2h19v2H23.5z"/>',
        image: '<circle style="fill:#f3d55b" cx="18.931" cy="14.431" r="4.569"/><polygon style="fill:#88c057" points="6.5,39 17.5,39 49.5,39 49.5,28 39.5,18.5 29,30 23.517,24.517"/>',
        pdf: '<path d="M19.514,33.324L19.514,33.324c-0.348,0-0.682-0.113-0.967-0.326 c-1.041-0.781-1.181-1.65-1.115-2.242c0.182-1.628,2.195-3.332,5.985-5.068c1.504-3.296,2.935-7.357,3.788-10.75 c-0.998-2.172-1.968-4.99-1.261-6.643c0.248-0.579,0.557-1.023,1.134-1.215c0.228-0.076,0.804-0.172,1.016-0.172 c0.504,0,0.947,0.649,1.261,1.049c0.295,0.376,0.964,1.173-0.373,6.802c1.348,2.784,3.258,5.62,5.088,7.562 c1.311-0.237,2.439-0.358,3.358-0.358c1.566,0,2.515,0.365,2.902,1.117c0.32,0.622,0.189,1.349-0.39,2.16 c-0.557,0.779-1.325,1.191-2.22,1.191c-1.216,0-2.632-0.768-4.211-2.285c-2.837,0.593-6.15,1.651-8.828,2.822 c-0.836,1.774-1.637,3.203-2.383,4.251C21.273,32.654,20.389,33.324,19.514,33.324z M22.176,28.198 c-2.137,1.201-3.008,2.188-3.071,2.744c-0.01,0.092-0.037,0.334,0.431,0.692C19.685,31.587,20.555,31.19,22.176,28.198z M35.813,23.756c0.815,0.627,1.014,0.944,1.547,0.944c0.234,0,0.901-0.01,1.21-0.441c0.149-0.209,0.207-0.343,0.23-0.415 c-0.123-0.065-0.286-0.197-1.175-0.197C37.12,23.648,36.485,23.67,35.813,23.756z M28.343,17.174 c-0.715,2.474-1.659,5.145-2.674,7.564c2.09-0.811,4.362-1.519,6.496-2.02C30.815,21.15,29.466,19.192,28.343,17.174z M27.736,8.712c-0.098,0.033-1.33,1.757,0.096,3.216C28.781,9.813,27.779,8.698,27.736,8.712z"/>',
        powerpoint: '<path style="fill:#c8bdb8" d="M39.5,30h-24V14h24V30z M17.5,28h20V16h-20V28z"/><path style="fill:#c8bdb8" d="M20.499,35c-0.175,0-0.353-0.046-0.514-0.143c-0.474-0.284-0.627-0.898-0.343-1.372l3-5 c0.284-0.474,0.898-0.627,1.372-0.343c0.474,0.284,0.627,0.898,0.343,1.372l-3,5C21.17,34.827,20.839,35,20.499,35z"/><path style="fill:#c8bdb8" d="M34.501,35c-0.34,0-0.671-0.173-0.858-0.485l-3-5c-0.284-0.474-0.131-1.088,0.343-1.372 c0.474-0.283,1.088-0.131,1.372,0.343l3,5c0.284,0.474,0.131,1.088-0.343,1.372C34.854,34.954,34.676,35,34.501,35z"/><path style="fill:#c8bdb8" d="M27.5,16c-0.552,0-1-0.447-1-1v-3c0-0.553,0.448-1,1-1s1,0.447,1,1v3C28.5,15.553,28.052,16,27.5,16 z"/><rect x="17.5" y="16" style="fill:#d3ccc9" width="20" height="12"/>',
        text: '<path d="M12.5,13h6c0.553,0,1-0.448,1-1s-0.447-1-1-1h-6c-0.553,0-1,0.448-1,1S11.947,13,12.5,13z"/><path d="M12.5,18h9c0.553,0,1-0.448,1-1s-0.447-1-1-1h-9c-0.553,0-1,0.448-1,1S11.947,18,12.5,18z"/><path d="M25.5,18c0.26,0,0.52-0.11,0.71-0.29c0.18-0.19,0.29-0.45,0.29-0.71c0-0.26-0.11-0.52-0.29-0.71 c-0.38-0.37-1.04-0.37-1.42,0c-0.181,0.19-0.29,0.44-0.29,0.71s0.109,0.52,0.29,0.71C24.979,17.89,25.24,18,25.5,18z"/><path d="M29.5,18h8c0.553,0,1-0.448,1-1s-0.447-1-1-1h-8c-0.553,0-1,0.448-1,1S28.947,18,29.5,18z"/><path d="M11.79,31.29c-0.181,0.19-0.29,0.44-0.29,0.71s0.109,0.52,0.29,0.71 C11.979,32.89,12.229,33,12.5,33c0.27,0,0.52-0.11,0.71-0.29c0.18-0.19,0.29-0.45,0.29-0.71c0-0.26-0.11-0.52-0.29-0.71 C12.84,30.92,12.16,30.92,11.79,31.29z"/><path d="M24.5,31h-8c-0.553,0-1,0.448-1,1s0.447,1,1,1h8c0.553,0,1-0.448,1-1S25.053,31,24.5,31z"/><path d="M41.5,18h2c0.553,0,1-0.448,1-1s-0.447-1-1-1h-2c-0.553,0-1,0.448-1,1S40.947,18,41.5,18z"/><path d="M12.5,23h22c0.553,0,1-0.448,1-1s-0.447-1-1-1h-22c-0.553,0-1,0.448-1,1S11.947,23,12.5,23z"/><path d="M43.5,21h-6c-0.553,0-1,0.448-1,1s0.447,1,1,1h6c0.553,0,1-0.448,1-1S44.053,21,43.5,21z"/><path d="M12.5,28h4c0.553,0,1-0.448,1-1s-0.447-1-1-1h-4c-0.553,0-1,0.448-1,1S11.947,28,12.5,28z"/><path d="M30.5,26h-10c-0.553,0-1,0.448-1,1s0.447,1,1,1h10c0.553,0,1-0.448,1-1S31.053,26,30.5,26z"/><path d="M43.5,26h-9c-0.553,0-1,0.448-1,1s0.447,1,1,1h9c0.553,0,1-0.448,1-1S44.053,26,43.5,26z"/>',
        video: '<path d="M24.5,28c-0.166,0-0.331-0.041-0.481-0.123C23.699,27.701,23.5,27.365,23.5,27V13 c0-0.365,0.199-0.701,0.519-0.877c0.321-0.175,0.71-0.162,1.019,0.033l11,7C36.325,19.34,36.5,19.658,36.5,20 s-0.175,0.66-0.463,0.844l-11,7C24.874,27.947,24.687,28,24.5,28z M25.5,14.821v10.357L33.637,20L25.5,14.821z"/><path d="M28.5,35c-8.271,0-15-6.729-15-15s6.729-15,15-15s15,6.729,15,15S36.771,35,28.5,35z M28.5,7 c-7.168,0-13,5.832-13,13s5.832,13,13,13s13-5.832,13-13S35.668,7,28.5,7z"/>'
      };

    function d(a, b, c) {
      return '<svg viewBox="0 0 48 48" class="svg-folder ' + a + '"><path class="svg-folder-bg" d="M40 12H22l-4-4H8c-2.2 0-4 1.8-4 4v8h40v-4c0-2.2-1.8-4-4-4z"/><path class="svg-folder-fg" d="M40 12H8c-2.2 0-4 1.8-4 4v20c0 2.2 1.8 4 4 4h32c2.2 0 4-1.8 4-4V16c0-2.2-1.8-4-4-4z"/>' + (b ? '<path class="svg-folder-symlink" d="M 39.231 23.883 L 28.485 32.862 L 28.485 14.902 Z"/><path class="svg-folder-symlink" d="M 10.065 30.022 L 10.065 40 L 16.205 40 L 16.205 30.022 C 16.205 28.334 17.587 26.953 19.275 26.953 L 32.323 26.953 L 32.323 20.812 L 19.275 20.812 C 14.21 20.812 10.065 24.956 10.065 30.022 Z"/>' : "") + (c ? "" : '<path class="svg-folder-forbidden" d="M 34.441 26.211 C 34.441 31.711 29.941 36.211 24.441 36.211 C 18.941 36.211 14.441 31.711 14.441 26.211 C 14.441 20.711 18.941 16.211 24.441 16.211 C 29.941 16.211 34.441 20.711 34.441 26.211"/><path style="fill:#FFF;" d="M 22.941 19.211 L 25.941 19.211 L 25.941 28.211 L 22.941 28.211 Z M 22.941 19.211"/><path style="fill:#FFF;" d="M 22.941 30.211 L 25.941 30.211 L 25.941 33.211 L 22.941 33.211 Z M 22.941 30.211"/>') + "</svg>"
    }
    a.word = a.text;
    var b = {
        application: ["app", "exe"],
        archive: ["gz", "zip", "7z", "7zip", "arj", "rar", "gzip", "bz2", "bzip2", "tar", "x-gzip"],
        cd: ["dmg", "iso", "bin", "cd", "cdr", "cue", "disc", "disk", "dsk", "dvd", "dvdr", "hdd", "hdi", "hds", "hfs", "hfv", "ima", "image", "imd", "img", "mdf", "mdx", "nrg", "omg", "toast", "cso", "mds"],
        code: ["php", "x-php", "js", "css", "xml", "json", "html", "htm", "py", "jsx", "scss", "clj", "less", "rb", "sql", "ts", "yml"],
        excel: ["xls", "xlt", "xlm", "xlsx", "xlsm", "xltx", "xltm", "xlsb", "xla", "xlam", "xll", "xlw", "csv"],
        font: ["ttf", "otf", "woff", "woff2", "eot", "ttc"],
        image: ["wbmp", "tiff", "webp", "psd", "ai", "eps", "jpg", "jpeg", "webp", "png", "gif", "bmp"],
        pdf: ["pdf"],
        powerpoint: ["ppt", "pot", "pps", "pptx", "pptm", "potx", "potm", "ppam", "ppsx", "ppsm", "sldx", "sldm"],
        text: ["epub", "rtf"],
        word: ["doc", "dot", "docx", "docm", "dotx", "dotm", "docb", "odt", "wbk"]
      },
      e = {};

    function g(a) {
      return a.hasOwnProperty("icon") ? a.icon : a.icon = function() {
        if (a.mime0 && ["archive", "audio", "image", "video"].includes(a.mime0)) return a.mime0;
        var b = !!a.mime1 && e[a.mime1];
        return b || !!a.ext && e[a.ext] || "text" === a.mime0 && "text"
      }()
    }
    i(Object.keys(b), function(a) {
      i(b[a], function(b) {
        e[b] = a
      })
    }), f.get_svg_icon = function(a) {
      return '<svg viewBox="0 0 24 24" class="svg-icon svg-' + a + '"><path class="svg-path-' + a + '" d="' + c[a] + '" /></svg>'
    }, f.get_svg_icon_class = function(a, b) {
      return '<svg viewBox="0 0 24 24" class="' + b + '"><path class="svg-path-' + a + '" d="' + c[a] + '" /></svg>'
    }, f.get_svg_icon_multi = function() {
      for (var a = arguments, e = a.length, d = "", b = 0; b < e; b++) d += '<path class="svg-path-' + a[b] + '" d="' + c[a[b]] + '" />';
      return '<svg viewBox="0 0 24 24" class="svg-icon svg-' + a[0] + '">' + d + "</svg>"
    }, f.get_svg_icon_multi_class = function(e) {
      for (var b = arguments, f = b.length, d = "", a = 1; a < f; a++) d += '<path class="svg-path-' + b[a] + '" d="' + c[b[a]] + '" />';
      return '<svg viewBox="0 0 24 24" class="' + e + '">' + d + "</svg>"
    }, f.get_svg_icon_files = function(a) {
      return a.is_dir ? d("svg-icon", a.is_link, a.is_readable) : f.get_svg_icon(a.is_pano ? "panorama_variant" : g(a) || "file_default")
    }, f.get_svg_large = function(b, f) {
      if (b.is_dir) return d(f, b.is_link, b.is_readable);
      var c = g(b),
        e = b.ext && b.ext.length < 6 ? b.ext : "image" === c && b.mime1;
      return '<svg viewBox="0 0 56 56" class="svg-file svg-' + (c || "none") + (f ? " " + f : "") + '"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"/><polygon  class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"/>' + (c ? '<g class="svg-file-icon">' + a[c] + "</g>" : "") + (e ? '<path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"/><text class="svg-file-ext' + (e.length > 3 ? " f_" + (15 - e.length) : "") + '" x="28" y="51.5">' + e + "</text>" : "") + (b.is_readable ? "" : '<path class="svg-file-forbidden" d="M 40.691 24.958 C 40.691 31.936 34.982 37.645 28.003 37.645 C 21.026 37.645 15.317 31.936 15.317 24.958 C 15.317 17.98 21.026 12.271 28.003 12.271 C 34.982 12.271 40.691 17.98 40.691 24.958"/><path style="fill: #FFF;" d="M 26.101 16.077 L 29.907 16.077 L 29.907 27.495 L 26.101 27.495 Z M 26.101 16.077"/><path style="fill: #FFF;" d="M 26.101 30.033 L 29.907 30.033 L 29.907 33.839 L 26.101 33.839 Z M 26.101 30.033"/>') + "</svg>"
    }
  }(),
  function() {
    ag.topbar_breadcrumbs = _id("topbar-breadcrumbs"), ag.breadcrumbs_info = ag.topbar_breadcrumbs.firstElementChild;
    var a, c = (ag.topbar_breadcrumbs.insertAdjacentHTML("afterbegin", '<button id="folder-actions" class="context-button" style="display:none">' + f.get_svg_icon("folder_cog_outline") + "</button>"), s(a = ag.topbar_breadcrumbs.firstElementChild, b => f.create_contextmenu(b, "topbar", a, _c.current_dir)), a);

    function b(a, b) {
      return '<span class="crumb"><a href="' + F(a) + '" data-path="' + y(a) + '" class="crumb-link">' + b + "</a></span>"
    }
    f.breadcrumbs_info = function() {
      var a = _c.current_dir,
        b = _c.files_count,
        d = b && a.images_count === b ? "images" : b && !a.files_count ? "folders" : "files";
      ag.breadcrumbs_info.innerHTML = b + ' <span data-lang="' + d + '" class="breadcrumbs-info-type">' + aj.get(d) + "</span>" + (a.dirsize ? '<span class="breadcrumbs-info-size">' + filesize(a.dirsize) + "</span>" : ""), W(ag.breadcrumbs_info), W(c)
    }, ag.breadcrumbs = _id("breadcrumbs");
    var d = [],
      e = [];

    function g(b, a) {
      var c = {
        targets: b,
        translateX: a ? [0, -2] : [-2, 0],
        opacity: a ? [1, 0] : [0, 1],
        easing: "easeOutQuad",
        duration: 150,
        delay: anime.stagger(Math.round(100 / b.length))
      };
      a && (c.complete = function() {
        H(b, ag.breadcrumbs), h()
      }), anime(c)
    }

    function h() {
      var f = "",
        a = [],
        c = "";
      d.length && i(d, function(d, g) {
        f += f ? "/" + d : d, (a.length || d !== e[g]) && (c += b(f, z(d)), a.push(g + 1))
      }), a.length && (ag.breadcrumbs.insertAdjacentHTML("beforeend", c), g(function(b, e) {
        for (var c = [], f = b.length, a = 0; a < f; a++) {
          var d = e(b[a], a);
          d && c.push(d)
        }
        return c
      }(a, function(a) {
        return ag.breadcrumbs.children[a]
      }))), e = d.slice(0), ag.breadcrumbs.lastChild != ag.breadcrumbs.firstChild && ag.breadcrumbs.lastChild.classList.add("crumb-active")
    }
    ag.breadcrumbs.innerHTML = b("", f.get_svg_icon("home")), f.set_breadcrumbs = function(b) {
      if (W(c, !0), W(ag.breadcrumbs_info, !0), d = b.split("/").filter(Boolean), e.length) {
        var a = [];
        i(e, function(c, b) {
          (a.length || c !== d[b]) && a.unshift(ag.breadcrumbs.children[b + 1])
        }), a.length ? g(a, !0) : (ag.breadcrumbs.lastChild.classList.remove("crumb-active"), h())
      } else h()
    }, s(ag.breadcrumbs, function(a) {
      "A" !== a.target.nodeName || Q(a, a.target) || f.get_files(a.target.dataset.path, "push")
    })
  }(), _c.prevent_right_click && (s(document, function(a) {
      ("IMG" === a.target.nodeName || "VIDEO" === a.target.nodeName || a.target.closest(".menu-li") || a.target.closest(".files-a")) && a.preventDefault()
    }, "contextmenu"), document.documentElement.style.setProperty("--touch-callout", "none")),
    function() {
      var b = _c.config.contextmenu || {},
        c = u.contextmenu = {},
        a = ag.contextmenu = _id("contextmenu");

      function d(c, a, d, e, b) {
        return e ? '<button class="dropdown-item' + (b ? " " + b : "") + '" data-action="' + d + '">' + (a ? f.get_svg_icon(a) : "") + aj.span(c, !0) + "</button>" : ""
      }
      var g = Object.assign({
        javascript: !0,
        current_dir_only: !0
      }, _c.config && _c.config.download_dir ? _c.config.download_dir : {});

      function h() {
        c.is_open && i()
      }

      function i(b) {
        if (b != c.is_open) {
          var d = (b ? "add" : "remove") + "EventListener";
          document.documentElement[d]("click", h), document[d]("contextmenu", h), document[d]("visibilitychange", h), window[d]("blur", h), window[d]("scroll", h), u.popup && u.popup.topbar && u.popup.topbar[d]("click", h), ag.sidebar_menu && ag.sidebar_menu[d]("scroll", h)
        }
        c.el.classList.toggle("cm-active", b), c.a && c.a.classList.toggle("cm-active", b), b != c.is_open && (anime.remove(a), anime({
          targets: a,
          opacity: b ? [0, 1] : 0,
          easing: "easeOutQuart",
          duration: 150,
          complete: b ? null : function() {
            a.style.cssText = null
          }
        })), c.is_open = !!b
      }
      f.create_contextmenu = function(k, l, j, h, s) {
        if ((_c.context_menu || "topbar" === l) && j && h) {
          if (c.is_open) {
            if (j == c.el) return k.preventDefault();
            c.el && c.el.classList.remove("cm-active"), c.a && c.a.classList.remove("cm-active")
          }
          k.stopPropagation(), j === c.el && h === c.item || (a.innerHTML = '<h6 class="dropdown-header" title="' + y(h.basename) + '">' + ("files" === l ? f.get_svg_icon_files(h) : "") + z(h.basename) + "</h6>" + function(){

               //if(h.browser_image && h.is_readable) {
                // console.log(k, l, j, h, s, document.location)
                 var href = (document.location+h.path).replace(document.location.search, '').replace('filesGallery', 'uploads');
                  return `<a class="dropdown-item" href="${href}" data-basename="${h.basename}" data-ext="${h.ext}" data-isimage="${h.image!==undefined}" onclick="postMsg(this);return false;">Insérer le média</a><hr style="border-top:1px solid #7f95a0">`
              // }
              // else {
              //   return '';
              // }

          }() + d("zoom", null, "popup", "popup" !== l && h.browser_image && h.is_readable) + d("Ouvrir", null, "folder", "sidebar" !== l && h.is_dir && h !== _c.current_dir) + d("show info", null, "modal", !["modal", "popup"].includes(l)) + (v = "dropdown-item", (w = !!(t = h) && E(t)) && "#" !== w ? '<a class="' + v + '" href="' + w.replace(_c.host, '').replace('//public', '') + '" target="_blank" data-lang="open in new tab">' + aj.get("open in new tab") + "</a>" : "") + d("copy link", null, "clipboard", e.url && e.clipboard) + (x = h, A = "dropdown-item", e.download && !x.is_dir && x.is_readable ? '<a href="' + D(x, !0).replace(_c.host, '').replace('//public', '') + '" class="' + A + '" data-lang="download" download>' + aj.get("download") + "</a>" : "") + C.a(h.gps, "dropdown-item", !0) + function(a) {
            if (!(e.download && _c.download_dir && a.is_dir && a.is_readable)) return "";
            var b = a.hasOwnProperty("files_count") ? a : _c.dirs[a.path];
            return !b || g.current_dir_only && b !== _c.current_dir || "files" === _c.download_dir && (!b.files_count || !e.is_pointer) || b.hasOwnProperty("files_count") && !b.files_count ? "" : "zip" !== _c.download_dir || g.javascript ? '<button class="dropdown-item fm-action" data-action="download_dir">' + f.get_svg_icon("tray_arrow_down") + aj.span("download", !0) + '&nbsp;<span class="no-pointer">' + ("zip" === _c.download_dir ? "Zip" : aj.get("files")) + "</span></button>" : '<a href="' + B(b).replace(_c.host, '').replace('//public', '') + '" class="dropdown-item fm-action" data-action="download_dir" download="' + (al ? y(a.basename) + ".zip" : "") + '">' + f.get_svg_icon("tray_arrow_down") + aj.span("download", !0) + '&nbsp;<span class="no-pointer">Zip</span></a>'
          }(h) + function() {
              if ("popup" === l || !h.is_writeable) return "";
              var a = d("delete", "close_thick", "delete", _c.allow_delete && h.path, "fm-action") + d("new folder", "plus", "new_folder", _c.allow_new_folder && h.is_dir, "fm-action") + d("new file", "plus", "new_file", _c.allow_new_file && h.is_dir, "fm-action") + d("rename", "pencil_outline", "rename", _c.allow_rename && h.path, "fm-action") + d("duplicate", "plus_circle_multiple_outline", "duplicate", _c.allow_duplicate && !h.is_dir, "fm-action") + d("upload", "tray_arrow_up", "upload", u.uppy && h.is_dir, "fm-action");
              return a ? '<div class="context-fm">' + a + "</div>" : ""
            }() + V(Object.keys(b), c => {
            var a = b[c];
            return d(a.text, a.icon, a.text, a.condition(h), "fm-action")
          })), a.style.display = "block";
          var t, v, w, x, A, _ = j.getBoundingClientRect(),
            m = (j.clientHeight > 50 ? k.clientY : _.top) - a.clientHeight - 10,
            n = j.clientHeight > 50 ? k.clientY + 20 : _.bottom + 10,
            o = m >= 0,
            p = !o && n + a.clientHeight <= document.documentElement.clientHeight;
          a.style.top = Math.round(p ? n : Math.max(0, m)) + "px";
          var q = (j.clientWidth > 100 ? k.clientX : _.left + j.offsetWidth / 2) - a.clientWidth / 2,
            r = Math.max(10, Math.min(document.documentElement.clientWidth - a.clientWidth - 10, q));
          a.style.left = Math.round(r) + "px", a.style.setProperty("--offset", Math.round(Math.max(10, Math.min(a.clientWidth - 10, a.clientWidth / 2 - r + q))) + "px"), a.classList.toggle("cm-top", o), a.classList.toggle("cm-bottom", p), a.classList.toggle("cm-border", "sidebar" === l), c.el = j, c.item = h, c.a = s || !1, i(!0), k.preventDefault()
        }
      }, I(a, function(a, d) {
        if (b[a]) b[a].action(c.item);
        else if (aq[a]) aq[a](c.item);
        else if ("upload" === a) ab(), u.uppy.setMeta({
          path: c.item.path
        }), u.uppy.getPlugin("Dashboard").openModal();
        else if ("popup" === a) _c.history && u.modal.open && u.modal.popstate.remove(), f.open_popup(c.item);
        else if ("folder" === a) u.modal.open && f.close_modal(), f.get_files(c.item.path, "push");
        else if ("modal" === a) f.open_modal(c.item, !0);
        else if ("clipboard" === a) h = E(c.item), i = new URL(h, location), P(i.href);
        else if ("download_dir" === a) {
          if (al && "zip" === _c.download_dir && !g.javascript) return;
          if (d.preventDefault(), !al) return ao.fire();
          if ("zip" === _c.download_dir) {
            var h, i, j = !0;
            Swal.fire({
              title: aj.get("download", !0) + ' zip <span class="swal-percent"></span>',
              toast: !0,
              showConfirmButton: !1,
              position: "bottom-right",
              customClass: {
                popup: "download-toast"
              },
              didOpen() {
                Swal.showLoading();
                var a = Swal.getContainer().getElementsByClassName("swal2-timer-progress-bar")[0],
                  b = Swal.getContainer().getElementsByClassName("swal-percent")[0];
                new jsFileDownloader({
                  url: B(c.item),
                  timeout: 6e5,
                  process: function(c) {
                    if (c.lengthComputable) {
                      var d = c.loaded / c.total;
                      a.style.transform = "scaleX(" + d + ")", b.textContent = Math.round(100 * d) + "%", c.loaded > 0 && j && (j = !1, Swal.hideLoading(), a.style.display = "block")
                    }
                  },
                  filename: c.item.basename + ".zip"
                }).then(function() {
                  Swal.close()
                }).catch(function(a) {
                  o("Download error", a), v.fire({
                    title: "No files to zip"
                  })
                })
              }
            })
          } else if ("files" === _c.download_dir) {
            var e = c.item.files || (_c.dirs[c.item.path] || {}).files || {};
            if (downloadables = [], index = 0, Object.keys(e).forEach(b => {
                var a = e[b];
                !a.is_dir && a.url_path && downloadables.push(encodeURI(a.url_path))
              }), !downloadables.length) return v.fire({
              title: "No files to download!"
            });
            ! function a() {
              new jsFileDownloader({
                url: downloadables[index]
              }).then(function() {
                (index++) < downloadables.length - 1 && a()
              })
            }()
          }
        }
      })
    }(), u.dropdown = {},
    function() {
      var a, b = e.pointer_events ? "pointerdown" : e.only_touch ? "touchstart" : "mousedown",
        c = e.pointer_events ? "pointerup" : "click";

      function d(c) {
        c.classList.contains("touch-open") ? a && a.remove() : a = S(document, b, function(b) {
          b.target.closest(".dropdown") !== c && (a.remove(), c.classList.remove("touch-open"), ag.files.style.pointerEvents = "none", setTimeout(function() {
            ag.files.style.pointerEvents = null
          }, 500))
        }), c.classList.toggle("touch-open")
      }
      e.is_touch && document.addEventListener("touchstart", function() {}, !0), f.dropdown = function(f, a, b) {
        e.only_pointer ? b && s(a, b) : e.only_touch || !e.pointer_events ? s(a, function() {
          d(f)
        }, c) : s(a, function(a) {
          "mouse" === a.pointerType ? b && b() : d(f)
        }, "pointerup"), e.is_dual_input ? e.pointer_events && s(a, function(a) {
          f.classList.toggle("mouse-hover", "mouse" === a.pointerType)
        }, "pointerover") : e.is_pointer && f.classList.add("mouse-hover")
      }
    }();
  var $ = Swal.mixin({
      input: "text",
      inputAttributes: {
        maxlength: 127,
        autocapitalize: "off",
        autocorrect: "off",
        autocomplete: "off",
        spellcheck: "false"
      },
      inputValidator(b) {
        var a = b.match(/[<>:"'/\\|?*#]|\.\.|\.$/g);
        if (a) return "Invalid characters " + a.join(" ")
      },
      scrollbarPadding: !1,
      closeButtonHtml: f.get_svg_icon("close"),
      showCloseButton: !0
    }),
    p = Swal.mixin({
      toast: !0,
      showConfirmButton: !1,
      timerProgressBar: !0,
      didOpen(a) {
        a.addEventListener("mouseenter", Swal.stopTimer), a.addEventListener("mouseleave", Swal.resumeTimer), a.addEventListener("click", Swal.close)
      }
    }),
    an = p.mixin({
      icon: "success",
      title: "Success",
      position: "bottom-right",
      timer: 2e3,
      customClass: {
        popup: "success-toast"
      }
    }),
    v = p.mixin({
      icon: "error",
      title: "Error",
      timer: 3e3,
      customClass: {
        popup: "error-toast"
      }
    }),
    ao = v.mixin({
      title: t("TGljZW5zZSByZXF1aXJlZA==")
    }),
    ap = Swal.mixin({
      title: "Confirm?",
      showCloseButton: !0,
      showCancelButton: !0,
      scrollbarPadding: !1
    }),
    aq = function() {
      function a(d, a, f, h) {
        if (!aq[d]) return v.fire({
          title: d + " is not available"
        });
        if (!_c["allow_" + d]) return v.fire({
          title: d + " is not allowed"
        });
        if (_c.demo_mode) return v.fire({
          title: "Not allowed in demo mode"
        });
        if (!al) return ao.fire();
        if (!a.is_writeable && ["delete", "rename", "new_folder", "new_file"].includes(d)) return v.fire({
          title: a.path + " is not writeable"
        });
        var e = !!_c.files[a.basename] && u.list.get("path", a.path)[0],
          g = !!e && b(e.elm),
          i = !(!a.is_dir || !ag.sidebar_menu) && ("" === a.path ? ag.sidebar_menu : b(_query('[data-path="' + c(a.path) + '"]', ag.sidebar_menu))),
          j = g ? _c.current_dir : _c.dirs[a.path.substring(0, a.path.lastIndexOf("/"))];
        Y({
          params: "action=fm&task=" + d + (a.is_dir ? "&is_dir=1" : "") + "&path=" + encodeURIComponent(a.path) + (f || ""),
          json_response: !0,
          fail: function() {
            return v.fire()
          },
          always: function() {
            g && g.classList.remove("fm-processing"), i && i.classList.remove("fm-processing"), ag.files.parentElement.classList.remove("fm-processing")
          },
          complete: function(b, c, f) {
            return o("fm:task:" + d, b, a), f && b && c ? b.error ? v.fire({
              title: b.error
            }) : b.success ? (u.contextmenu.item === a && delete u.contextmenu.el, void(h && h(e, g, i, j, b))) : v.fire() : v.fire()
          }
        })
      }

      function b(a) {
        return !!a && (a.style.removeProperty("opacity"), a.classList.add("fm-processing"), a)
      }

      function c(a) {
        return CSS.escape ? CSS.escape(a) : a.replace(/["\\]/g, "\\$&")
      }

      function d(a, b) {
        return "string" == typeof a && ("" === a ? b : a + (a.endsWith("/") ? "" : "/") + b)
      }

      function g(b) {
        if (!b) return "";
        var a = b.split("/"),
          c = a.pop("/");
        return '<span class="swal-files-path">' + (a.length ? a.join("/") + "/" : "") + '<span class="swal-files-name' + (a.length ? " swal-files-has-path" : "") + '">' + c + "</span></span>"
      }
      return {
        duplicate: function(b) {
          if (b.is_dir) return v.fire({
            title: "Can't duplicate folders"
          });
          $.fire({
            title: aj.get("Duplicate", !0),
            html: g(b.path),
            inputPlaceholder: aj.get("Duplicate name", !0),
            inputValue: b.basename,
            inputValidator(a) {
              var b = a.match(/[<>:"'/\\|?*#]|\.\.|\.$/g);
              return b ? "Invalid characters " + b.join(" ") : _c.files[a] ? "File already exists" : void 0
            }
          }).then(c => {
            c.isConfirmed && c.value && c.value !== b.basename && a("duplicate", b, "&name=" + encodeURI(c.value), function(b, c, d, a) {
              a && (delete a.files, delete a.html, delete a.json_cache, j.remove(at(a.path, a.mtime)), a === _c.current_dir && f.get_files(_c.current_path, "replace", !0))
            })
          })
        },
        rename: function(b) {
          $.fire({
            title: aj.get("rename", !0),
            html: g(b.path),
            inputPlaceholder: aj.get("new name", !0),
            inputValue: b.basename,
            inputValidator(a) {
              if (a === b.basename) return !1;
              var c = a.match(/[<>:"'/\\|?*#]|\.\.|\.$/g);
              if (c) return "Invalid characters " + c.join(" ");
              if (_c.files[b.basename] && _c.files[b.basename].path === b.path) {
                if (_c.files[a]) return (b.is_dir ? "Folder" : "File") + " already exists"
              } else if (b.is_dir) {
                var e = b.path.split("/").slice(0, -1).join("/");
                if (_c.dirs[d(e, a)]) return "Folder already existsA"
              }
            }
          }).then(g => {
            if (g.isConfirmed && g.value && g.value !== b.basename) {
              var h = g.value;
              a("rename", b, "&name=" + encodeURI(h), function(p, g, s, a) {
                an.fire({
                  title: h
                });
                var m = b.basename,
                  n = b.path,
                  q = d(a ? a.path : n.split("/").slice(0, -1).join("/"), h),
                  r = !!a && d(a.url_path, h);
                if (a) {
                  if (a === _c.current_dir && a.files) {
                    var i = a.files[h] = Object.assign(b, {
                      basename: h,
                      path: q,
                      url_path: r
                    });
                    if (g && g.isConnected) {
                      g.setAttribute("href", D(i, "download" === _c.click)), g.dataset.name = h, _class("name", g)[0].textContent = h;
                      var k = g.firstElementChild;
                      if (!b.is_dir && "IMG" === k.nodeName) {
                        var o = _c.script + "?file=" + encodeURIComponent(i.path) + "&resize=" + (e.pixel_ratio >= 1.5 && _c.image_resize_dimensions_retina ? _c.image_resize_dimensions_retina : _c.image_resize_dimensions) + "&" + (new Date).getTime();
                        k.dataset.src = o, k.hasAttribute("src") && k.setAttribute("src", o)
                      }
                      p._values = i, f.set_sort()
                    }
                    delete i.popup_caption, delete a.files[m]
                  } else delete a.files;
                  if (a.preview === m) {
                    a.preview = h;
                    var l = a.path.split("/").slice(0, -1).join("/");
                    l && _c.dirs[l] && delete _c.dirs[l].html
                  }
                  delete a.html, delete a.json_cache, j.remove(at(a.path, a.mtime))
                }
                b.is_dir && (Object.keys(_c.dirs).filter(a => a.startsWith(n)).forEach(function(a) {
                  var f = a.split(n).slice(1).join("/"),
                    d = q + f,
                    e = _c.dirs[d] = Object.assign(_c.dirs[a], {
                      path: d,
                      files: !1,
                      json_cache: !1,
                      html: !1,
                      url_path: !!r && r + f
                    });
                  if (a === n && (e.basename = h), delete _c.dirs[a], j.remove(at(a, e.mtime)), ag.sidebar_menu) {
                    var b = _query('[data-path="' + c(a) + '"]', ag.sidebar_menu);
                    b && (a === n && (b.firstElementChild.lastChild.textContent = h), b.dataset.path = d, b.firstElementChild.setAttribute("href", D(e)))
                  }
                }), _c.current_path && _c.current_path.startsWith(n) && f.get_files(_c.current_dir.path, "push"))
              })
            }
          })
        },
        new_folder: function(b) {
          if (!b.is_dir) return v.fire({
            title: b.basename + " is not a directory"
          });
          $.fire({
            title: aj.get("new folder", !0),
            html: g(b.path),
            inputPlaceholder: aj.get("Folder name", !0),
            inputValidator(a) {
              var c = a.match(/[<>:"'/\\|?*#]|\.\.|\.$/g);
              return c ? "Invalid characters " + c.join(" ") : _c.dirs[d(b.path, a)] || _c.dirs[b.path] && _c.dirs[b.path].files && _c.dirs[b.path].files[a] ? "Folder exists" : void 0
            }
          }).then(c => {
            if (c.isConfirmed && c.value) {
              var e = c.value;
              a("new_folder", b, "&name=" + encodeURI(e), function(m, n, c, o) {
                if (an.fire({
                    title: e
                  }), _c.menu_enabled && !_c.menu_exists) return window.location.reload();
                var a = _c.dirs[b.path];
                if (a) {
                  if (delete a.files, delete a.html, delete a.json_cache, j.remove(at(a.path, a.mtime)), c) {
                    var g = d(a.path, e),
                      h = _c.dirs[g] = {
                        basename: e,
                        path: g,
                        url_path: d(a.url_path, e),
                        is_dir: !0,
                        is_writeable: !0,
                        is_readable: !0,
                        filetype: "dir",
                        mime: "directory",
                        mtime: Date.now() / 1e3,
                        fileperms: a.fileperms
                      },
                      i = "UL" === c.lastElementChild.nodeName && c.lastElementChild,
                      k = 1 * (c.dataset.level || 0),
                      l = '<li data-level="' + (k + 1) + '" data-path="' + y(g) + '" class="menu-li"><a href="' + D(h) + '" class="menu-a">' + f.get_svg_icon_class("folder", "menu-icon menu-icon-folder") + z(e) + "</a></li>";
                    i ? i.insertAdjacentHTML("afterbegin", l) : (c.firstElementChild.firstElementChild.remove(), c.firstElementChild.insertAdjacentHTML("afterbegin", f.get_svg_icon_multi_class("menu-icon menu-icon-toggle", "plus", "minus") + f.get_svg_icon_multi_class("menu-icon menu-icon-folder menu-icon-folder-toggle", "folder", "folder_plus", "folder_minus")), c.classList.add("has-ul"), c.insertAdjacentHTML("beforeend", '<ul style="--depth:' + k + '" class="menu-ul">' + l + "</ul>")), h.menu_li = c.lastElementChild.firstElementChild
                  }
                  a === _c.current_dir && f.get_files(_c.current_path, "replace", !0)
                }
              })
            }
          })
        },
        new_file: function(b) {
          if (!b.is_dir) return v.fire({
            title: b.basename + " is not a directory"
          });
          $.fire({
            title: aj.get("new file", !0),
            html: g(b.path),
            inputPlaceholder: aj.get("File name", !0),
            inputValue: "file.txt",
            inputValidator(a) {
              var c = a.match(/[<>:"'/\\|?*#]|\.\.|\.$/g);
              return c ? "Invalid characters " + c.join(" ") : _c.dirs[b.path] && _c.dirs[b.path].files && _c.dirs[b.path].files[a] ? "Filename exists" : void 0
            }
          }).then(c => {
            if (c.isConfirmed && c.value) {
              var d = c.value;
              a("new_file", b, "&name=" + encodeURI(d), function(c, e, g, h) {
                an.fire({
                  title: d
                });
                var a = _c.dirs[b.path];
                a && (delete a.files, delete a.html, delete a.json_cache, j.remove(at(a.path, a.mtime)), a === _c.current_dir && f.get_files(_c.current_path, "replace", !0))
              })
            }
          })
        },
        delete: function(b) {
          ap.fire({
            title: aj.get("delete", !0),
            html: g(b.path)
          }).then(c => {
            c.isConfirmed && a("delete", b, null, function(l, m, c, a, g) {
              if (g.fail) return v.fire({
                title: "Failed to delete " + g.fail + " items. Please refresh browser.",
                timer: !1
              });
              if (an.fire({
                  title: aj.get("delete", !0) + " " + b.basename
                }), a.files && delete a.files[b.basename], delete a.html, delete a.json_cache, j.remove(at(a.path, a.mtime)), "image" === b.mime0 && a.images_count && a.images_count--, !b.is_dir && a.files_count && a.files_count--, a.dirsize && b.filesize && (a.dirsize -= b.filesize), a.preview === b.basename && (delete a.preview, a.path)) {
                var h = a.path.split("/").slice(0, -1).join("/");
                _c.dirs[h] && delete _c.dirs[h].html
              }
              if (a === _c.current_dir && (_c.file_names = Object.keys(_c.files), _c.files_count = _c.file_names.length, f.breadcrumbs_info(), u.list.remove("path", b.path), x()), b.is_dir) {
                if (Object.keys(_c.dirs).forEach(a => {
                    if (!a.startsWith(b.path)) return !0;
                    var c = _c.dirs[a];
                    c && (j.remove(at(c.path, c.mtime)), delete _c.dirs[a])
                  }), c && c.isConnected) {
                  var d = c.parentElement;
                  if (d.children.length > 1 || "LI" !== d.parentElement.tagName) c.remove();
                  else {
                    var i = d.parentElement;
                    d.remove(), i.classList.remove("has-ul", "menu-li-open");
                    var k = i.firstElementChild;
                    k.firstElementChild.remove();
                    var e = k.firstElementChild;
                    e.lastElementChild.remove(), e.lastElementChild.remove(), e.classList.remove("menu-icon-folder-toggle")
                  }
                }
                _c.current_path && _c.current_path.startsWith(b.path) && f.get_files(a.path, "replace")
              }
            })
          })
        }
      }
    }();

  function ar(c, a) {
    try {
      var b = JSON.parse(c);
      return a ? b[a] : b
    } catch (d) {
      return !1
    }
  }
  _c.allow_upload && f.load_plugin("uppy", function() {
    var b = {
      note: !0,
      DropTarget: !0,
      ImageEditor: !0
    };
    _c.config && _c.config.uppy && Object.assign(b, _c.config.uppy);
    var i = u.uppy = new Uppy.Core({
        restrictions: {
          maxFileSize: _c.upload_max_filesize || null,
          allowedFileTypes: _c.upload_allowed_file_types ? _c.upload_allowed_file_types.split(",").map(b => {
            var a = b.trim();
            return a.startsWith(".") || a.includes("/") || a.includes("*") ? a : "." + a
          }).filter(a => a) : null
        },
        meta: {
          action: "fm",
          task: "upload",
          is_dir: !0
        }
      }).use(Uppy.Dashboard, {
        trigger: "#fm-upload",
        thumbnailWidth: Math.round(160 * Math.min(e.pixel_ratio, 2)),
        doneButtonHandler() {
          k(!1), i.reset()
        },
        showLinkToFileUploadResult: !0,
        showProgressDetails: !0,
        showRemoveButtonAfterComplete: _c.allow_delete,
        metaFields: [{
          id: "name",
          name: aj.get("name"),
          placeholder: aj.get("name"),
          render: ({
            value: a,
            onChange: d,
            fieldCSSClasses: b
          }, c) => c("input", {
            class: b.text,
            type: "text",
            value: a,
            maxlength: 128,
            onChange: a => d(a.target.value.trim()),
            onInput(a) {
              a.target.value = a.target.value.replace(/[#%&(){}\\<>*?/$!'":;\[\]@+`|=]/gi, "").replace("..", ".")
            },
            "data-uppy-super-focusable": !0
          })
        }],
        closeModalOnClickOutside: !0,
        animateOpenClose: !1,
        proudlyDisplayPoweredByUppy: !1,
        theme: "dark"
      }).use(Uppy.XHRUpload, {
        endpoint: _c.script,
        validateStatus: (b, a, c) => ar(a, "success"),
        getResponseError: (a, b) => ar(a, "error")
      }).on("file-removed", (a, b) => {
        _c.allow_delete && "removed-by-user" === b && a.response && a.response.body && a.response.body.success && a.progress && a.progress.uploadComplete && a.meta && Y({
          params: "action=fm&task=delete&path=" + encodeURIComponent(a.meta.path + "/" + a.meta.name)
        })
      }).on("upload-success", (b, c) => {
        var a = c.body.filename;
        a && b.name !== a && i.setFileMeta(b.id, {
          name: a
        })
      }).on("complete", b => {
        b.successful && b.successful.length && b.successful.forEach(function(a) {
          a.uploadURL && (a.uploadURL = new URL(a.uploadURL, location.href).href)
        });
        var c = i.getState().meta.path,
          a = _c.dirs[c];
        a && (delete a.files, delete a.html, delete a.json_cache, j.remove(at(c, a.mtime))), delete u.contextmenu.el
      }).on("dashboard:modal-open", () => {
        al || c.classList.add("uppy-nolicense__stOneHACK__"), b.note && i.getPlugin("Dashboard").setOptions({
          note: ("string" == typeof b.note ? b.note : "%path% \xe2\u2030\xa4 %upload_max_filesize%").replace("%upload_max_filesize%", _c.upload_max_filesize ? filesize(_c.upload_max_filesize) : "").replace("%path%", i.getState().meta.path || _c.current_path || "/")
        })
      }).on("dashboard:modal-closed", () => {
        var a = i.getState();
        if (100 === a.totalProgress) {
          var b = a.meta.path === _c.current_path;
          f.get_files(a.meta.path, b ? "replace" : "push", b), i.reset()
        }
      }),
      g = {
        ImageEditor: {
          target: Uppy.Dashboard,
          quality: .8
        },
        DropTarget: {
          target: document.body,
          onDrop: a => k(!0, _c.current_path)
        },
        Webcam: {
          target: Uppy.Dashboard
        }
      };
    Object.keys(g).forEach(a => {
      b[a] && i.use(Uppy[a], "object" == typeof b[a] ? Object.assign(g[a], b[a]) : g[a])
    });
    var c = _class("uppy-Root")[0];

    function k(a, b) {
      var c = i.getPlugin("Dashboard");
      !!a !== c.isModalOpen() && (c[a ? "openModal" : "closeModal"](), b && i.setMeta({
        path: b
      }))
    }

    function h(a) {
      f.load_plugin("uppy_locale_" + a, function() {
        u.uppy.setOptions({
          locale: Uppy.locales[a]
        })
      }, {
        src: ["@uppy/locales@2.0.8/dist/" + a + ".min.js"]
      })
    }
    _c.demo_mode && c.classList.add("uppy-demo-mode"), _c.allow_delete && c.classList.add("uppy-allow-delete"), f.uppy_locale = function(c) {
      var b = a(c) || a(_c.lang_default) || "en_US";
      b !== d && h(d = b)
    };
    var l = {
      no: "nb_NO",
      nn: "nb_NO"
    };

    function a(a) {
      return !!a && l[a]
    } ["ar_SA", "bg_BG", "cs_CZ", "da_DK", "de_DE", "el_GR", "en_US", "es_ES", "fa_IR", "fi_FI", "fr_FR", "gl_ES", "he_IL", "hr_HR", "hu_HU", "id_ID", "is_IS", "it_IT", "ja_JP", "ko_KR", "nb_NO", "nl_NL", "pl_PL", "pt_BR", "pt_PT", "ro_RO", "ru_RU", "sk_SK", "sr_RS_Cyrillic", "sr_RS_Latin", "sv_SE", "th_TH", "tr_TR", "uk_UA", "vi_VN", "zh_CN", "zh_TW"].forEach(function(a) {
      l[a.replace("_", "-").toLowerCase()] = a;
      var b = a.split("_")[0];
      l[b] || (l[b] = a)
    });
    var d = a(n("lang", !0)) || a(j.get("files:lang:current")) || !!b.locale && a(b.locale.replace("_", "-").toLowerCase()) || function() {
      if (_c.lang_auto && e.nav_langs)
        for (var b = 0; b < e.nav_langs.length; b++) {
          var c = e.nav_langs[b].toLowerCase(),
            d = a(c);
          if (d) return d;
          var f = !!c.includes("-") && a(c.split("-")[0]);
          if (f) return f
        }
    }() || a(_c.lang_default) || "en_US";
    "en_US" !== d && h(d), s(window, a => {
      i.reset(), k(!1)
    }, "popstate")
  });
  var as = function() {
    var d, g = "",
      b = screen.width >= 768,
      c = _c.filter_live && e.is_pointer,
      h = function() {
        if (!_c.filter_props || "string" != typeof _c.filter_props) return ["basename"];
        var b = ["basename", "filetype", "mime", "features", "title", "headline", "description", "creator", "credit", "copyright", "keywords", "city", "sub-location", "province-state"],
          a = ["icon"];
        return _c.filter_props.split(",").forEach(function(d) {
          var c = d.trim().toLowerCase();
          "name" === c && (c = "basename"), c && b.includes(c) && !a.includes(c) && a.push(c)
        }), a
      }();

    function j(a) {
      b && (ag.filter_container.dataset.input = a || "")
    }
    var a = {
      create: function() {
        u.list = new List(ag.files.parentElement, {}), i(_c.file_names, function(a, b) {
          u.list.items[b]._values = _c.files[a]
        })
      },
      empty: function() {
        u.list && u.list.clear(), G(ag.files), window.scrollY && window.scroll({
          top: 0
        })
      },
      filter: function(c) {
        if (g !== ag.filter.value && u.list) {
          g = ag.filter.value;
          var a = u.list.search(g, h).length,
            b = g ? "filter-" + (a ? "" : "no") + "match" : "";
          ag.filter.className !== b && (ag.filter.className = b), f.topbar_info_search(g, a), !1 !== c && history.replaceState(history.state || null, document.title, g ? "#filter=" + encodeURIComponent(g) : location.pathname + location.search), window.scrollY && window.scrollTo({
            top: 0
          })
        }
      },
      hash: function(c) {
        var b = n("filter", !0, !0);
        b && (b = decodeURIComponent(b), ag.filter.value = b, j(b), c && a.filter(!1))
      },
      clear: function(b) {
        if (g) {
          if (ag.filter.value = "", j(), b) return a.filter();
          g = "", ag.filter.className = ""
        }
      },
      disabled: function(a) {
        !!a !== ag.filter.disabled && (ag.filter.disabled = !!a)
      }
    };
    return (b || c) && s(ag.filter, function(b) {
      j(ag.filter.value), c && (d && clearTimeout(d), d = setTimeout(a.filter, aa(250, 750, _c.files_count)))
    }, "input"), s(ag.filter, a.filter, "change"), a
  }();

  function at(a, b) {
    return "" === a && (a = "ROOT"), "files:dir:" + _c.dirs_hash + ":" + (a || _c.current_dir.path) + ":" + (b || _c.current_dir.mtime)
  }! function() {
    var a = !1,
      b = e.pixel_ratio >= 1.5 && _c.image_resize_dimensions_retina ? _c.image_resize_dimensions_retina : _c.image_resize_dimensions,
      c = !!_c.x3_path && _c.x3_path + (_c.x3_path.endsWith("/") ? "" : "/") + "render/w" + (e.pixel_ratio >= 1.5 ? "480" : "320") + "/";

    function d(c, b) {
      if (a) return a.abort(), a = !1, void(b && b());
      if (_c.transitions && _c.files_count) {
        if ("list" !== _c.layout) {
          for (var f = ag.files.children, i = f.length, d = [], j = window.innerHeight, e = 0; e < i; e++) {
            var g = f[e],
              h = g.getBoundingClientRect();
            if (!(h.bottom < 0)) {
              if (h.top < j - 10) d.push(g);
              else if ("columns" !== _c.layout) break
            }
          }
          var k = Math.min(Math.round(200 / d.length), 30);
          l = {
            targets: d,
            opacity: c ? [0, 1] : [1, 0],
            easing: "easeOutQuint",
            duration: c ? 300 : 150,
            delay: anime.stagger(k)
          }, b && (l.complete = b), anime(l)
        } else {
          anime.remove(ag.files);
          var l = {
            targets: ag.files,
            opacity: c ? [0, 1] : [1, 0],
            easing: "easeOutCubic",
            duration: c ? 300 : 150,
            complete: function() {
              c || ag.files.style.removeProperty("opacity"), b && b()
            }
          };
          anime(l)
        }
      } else b && b()
    }

    function g(a, d, c, f) {
      var e, b;
      x(a, d, f), c && _c.history && history[c + "State"]({
        path: a
      }, d, (e = c, b = a, h || "replace" !== e || !_c.query_path && b ? (h = !0, b ? "?" + encodeURI(b).replace(/&/g, "%26").replace(/#/g, "%23") : "//" + location.host + location.pathname) : location.href)), document.body.dataset.currentPath = a || "/"
    }
    image_load_errors = 0, image_resize_min_ratio = Math.max(_c.image_resize_min_ratio, 1), image_resize_types = _c.image_resize_enabled && _c.image_resize_types ? _c.image_resize_types.split(",").map(a => ({
      jpeg: 2,
      jpg: 2,
      png: 3,
      gif: 1,
      webp: 18,
      bmp: 6
    })[a.toLowerCase().trim()]).filter(a => a) : [], click_window = _c.click_window && !["download", "window"].includes(_c.click) ? _c.click_window.split(",").map(a => a.toLowerCase().trim()).filter(a => a) : [];
    var h = !1;

    function _(h, a, i) {
      am(), image_load_errors = 0, _c.current_dir = _c.dirs[h], _c.files = _c.current_dir.files, o(i + " :", h, _c.current_dir), _c.file_names = Object.keys(_c.files), _c.files_count = _c.file_names.length, g(h, _c.current_dir.basename, a), f.breadcrumbs_info(), _c.files_count || f.topbar_info(f.get_svg_icon("alert_circle_outline") + '<span data-lang="directory is empty" class="f-inline-block">' + aj.get("directory is empty") + "</span>", "warning"), as.disabled(!_c.files_count), W(ag.sortbar, !_c.files_count), _c.files_count && (ag.files.innerHTML = _c.current_dir.html || (_c.current_dir.html = V(_c.file_names, function(i, j) {
        var a = _c.files[i];
        if (!a.is_dir && (!a.mime && a.ext && (a.mime = au[a.ext]), a.mime)) {
          var d = a.mime.split("/");
          a.mime0 = d[0], d[1] && (a.mime1 = d[1])
        }
        a.image && (a.image.exif && a.image.exif.gps && Array.isArray(a.image.exif.gps) && (a.gps = a.image.exif.gps), a.image.width && a.image.height && (a.dimensions = [a.image.width, a.image.height], a.ratio = a.image.width / a.image.height), a.image.iptc && Object.assign(a, a.image.iptc));
        var g, h = function() {
          if (a.mime1) {
            if ("image" === a.mime0) {
              if (!e.browser_images.includes(a.mime1)) return;
              if (a.browser_image = a.mime1, a.is_popup = !0, al && a.dimensions && e.max_texture_size) {
                var h = _c.config.panorama.is_pano(a, e);
                h && (a.is_pano = h, _c.current_dir.has_pano = !0)
              }
              if (!_c.load_images || !a.is_readable) return;
              var d = !1,
                g = "files-img files-img-placeholder files-lazy";
              if ("svg+xml" === a.browser_image || "svg" === a.ext) {
                if (a.filesize > _c.config.load_svg_max_filesize) return;
                g += " files-img-svg"
              } else {
                if (function(c) {
                    if (_c.image_resize_enabled && c.dimensions && c.mime1 && c.image && _c.resize_image_types.includes(c.mime1)) {
                      var a = c.image,
                        d = Math.max(a.width, a.height) / b,
                        e = a.width * a.height;
                      if ((!a.type || image_resize_types.includes(a.type)) && !(d < image_resize_min_ratio && c.filesize <= _c.load_images_max_filesize || _c.image_resize_max_pixels && e > _c.image_resize_max_pixels)) {
                        if (_c.image_resize_memory_limit) {
                          var f = a.width / d,
                            g = a.height / d;
                          if ((e * (a.bits ? a.bits / 8 : 1) * (a.channels || 3) * 1.33 + f * g * 4) / 1048576 > _c.image_resize_memory_limit) return
                        }
                        return !0
                      }
                    }
                  }(a) && (d = a.resize = b), !d && a.filesize > _c.load_images_max_filesize) return;
                "ico" === a.ext && (g += " files-img-ico"), a.dimensions && (a.preview_dimensions = d ? a.ratio > 1 ? [d, Math.round(d / a.ratio)] : [Math.round(d * a.ratio), d] : [a.image.width, a.image.height], a.preview_ratio = a.preview_dimensions[0] / a.preview_dimensions[1])
              }
              return '<img class="' + g + '" data-src="' + function() {
                if (c) return c + encodeURI(a.path);
                if (d && a.image["resize" + d]) return encodeURI(a.image["resize" + d]);
                if (!d && !_c.load_files_proxy_php && a.url_path) return encodeURI(a.url_path).replace(/#/g, "%23");
                var b = a.mtime + "." + a.filesize;
                return _c.script + "?file=" + encodeURIComponent(a.path) + (d ? "&resize=" + d + "&" + _c.image_cache_hash + "." + b : "&" + b)
              }() + '"' + (a.preview_dimensions ? ' width="' + a.preview_dimensions[0] + '" height="' + a.preview_dimensions[1] + '"' : "") + ">"
            }
            return "video" === a.mime0 && (ah("video", a) && (a.is_browser_video = !0, _c.popup_video && (a.is_popup = !0)), _c.video_thumbs_enabled && a.is_readable) ? (a.preview_dimensions = [480, 320], a.preview_ratio = 1.5, (a.is_browser_video ? f.get_svg_icon("play") : "") + '<img class="files-img files-img-placeholder files-lazy" data-src="' + _c.script + "?file=" + encodeURIComponent(a.path) + "&resize=video&" + _c.image_cache_hash + "." + a.mtime + "." + a.filesize + '"' + (a.preview_dimensions ? ' width="' + a.preview_dimensions[0] + '" height="' + a.preview_dimensions[1] + '"' : "") + ">") : void 0
          }
        }();
        return function(c) {
          var a = "dir" == c.filetype ? "folders" : "files",
            b = c.image;
          if (b) {
            a += ",image";
            var d = b.width,
              e = b.height,
              f = b.exif,
              g = b.iptc;
            d && e && (a += d === e ? ",square" : d > e ? ",landscape,horizontal" : ",portrait,vertical"), f && (a += ",exif", f.gps && (a += ",gps,maps"), a += V(["Make", "Model", "Software"], function(a) {
              if (f[a]) return "," + f[a]
            })), g && (a += ",iptc" + V(["title", "headline", "description", "creator", "credit", "copyright", "keywords", "city", "sub-location", "province-state"], function(a) {
              if (g[a]) return "," + a
            }))
          }
          c.features = a
        }(a), a.DateTimeOriginal && (a.mtime = a.DateTimeOriginal), '<a href="' + E(a, "download" === _c.click).replace(_c.host, '').replace('//public', '') + '" target="_blank" class="files-a files-a-' + (h ? "img" : "svg") + '"' + (a.preview_ratio ? ' style="--ratio:' + a.preview_ratio + '"' : "") + ' data-name="' + y(a.basename) + '"' + (a.is_dir || "download" !== _c.click ? "" : " download") + ">" + (a.is_pano ? f.get_svg_icon_class("panorama_variant", "svg-icon files-icon-overlay") : "") + ("gif" !== a.browser_image || !a.resize && h ? "" : f.get_svg_icon_class("gif", "svg-icon files-icon-overlay")) + (h || f.get_svg_large(a, "files-svg")) + '<div class="files-data">' + C.span(a.gps, "gps") + '<span class="name" title="' + y(a.basename) + '">' + z(a.basename) + "</span>" + (a.image && a.image.iptc && a.image.iptc.title ? A(-1 === (g = a.image.iptc.title).indexOf("<a") ? g : g.replace(/<a\s/g, "<span ").replace(/<\/a>/g, "</span>").replace(/\shref\=/g, " data-href="), "title") : "") + A(f.get_svg_icon_files(a), "icon") + K(a.dimensions, "dimensions") + L(a, "size") + N(a.image, "exif", "span") + A(a.ext ? A(a.ext, "ext-inner") : "", "ext") + A(f.get_time(a, "L LT", !0, !1), "date") + '<span class="flex"></span></div>' + M("menu" !== _c.click || a.is_dir, "files-context") + ad(a) + "</a>"
      })), _c.current_dir.has_pano && f.load_plugin("pannellum"), as.create(), "push" !== a && as.hash(!0), f.set_sort(), !a && _c.current_dir.scroll && _c.current_dir.scroll.y && _c.current_dir.scroll.h == document.body.scrollHeight && window.scrollTo(0, _c.current_dir.scroll.y), "replace" === a && function(d) {
        if (_c.history && location.hash) {
          var b = n("pid", !0, !0),
            c = b || location.hash.replace("#", "");
          if (c) {
            var a = _c.files[decodeURIComponent(c)];
            if (a) return b && a.is_popup ? f.open_popup(a, !0) : f.open_modal(a), !0
          }
        }
      }() || d(!0))
    }

    function i(a, b) {
      return _c.dirs[a] ? b ? _c.dirs[a].mtime > b.mtime ? _c.dirs[a] = Object.assign(b, _c.dirs[a]) : Object.assign(_c.dirs[a], b) : _c.dirs[a] : _c.dirs[a] = b || {}
    }

    function k(a, b, c) {
      W(ag.sortbar, !0), f.topbar_info(f.get_svg_icon("alert_circle_outline") + '<strong data-lang="error" class="f-inline-block">' + aj.get("error") + "</strong>" + (a ? ": " + a : "."), "error"), g(b, aj.get("error") + (a ? ": " + a : "."), c, !0)
    }
    f.get_files = function(b, l, e) {
      if (e || b !== _c.current_path) {
        _c.current_path = b, _c.config.history_scroll && _c.current_dir && (_c.current_dir.scroll = {
          y: window.scrollY,
          h: document.body.scrollHeight
        }), ag.topbar_info.className = "info-hidden", as.clear(), e || f.set_breadcrumbs(b), !e && _c.menu_exists && f.set_menu_active(b);
        var c = _c.dirs[b];
        if (!e && c) {
          if (c.files) return d(!1, function() {
            as.empty(), _(b, l, "files from JS")
          });
          var g = j.get_json(at(b, c.mtime));
          if (g) return i(b, g), d(!1, function() {
            as.empty(), _(b, l, "files from localStorage")
          })
        }
        as.disabled(!0), _c.menu_exists && f.menu_loading(!1, !0), ag.topbar.classList.add("topbar-spinner");
        var m = 0,
          h = !(!c || !c.json_cache) && c.json_cache;
        d(!1, function() {
          as.empty(), n()
        }), a = Y({
          params: !h && "action=files&dir=" + encodeURIComponent(b),
          url: h,
          json_response: !0,
          fail() {
            k(b, b, l)
          },
          always() {
            a = !1, _c.menu_exists && f.menu_loading(!1, !1), ag.topbar.classList.remove("topbar-spinner")
          },
          complete: function(a, c, d) {
            return d ? a.error ? k(a.error + " " + b, b, l) : (i(b, a), j.set(at(b, a.mtime), c, !1, 1e3), void n()) : k(b, b, l)
          }
        })
      }

      function n(a) {
        1 == m++ && _(b, l, h ? "files from JSON " + h : "files from xmlhttp")
      }
    }, f.init_files = function() {
      if (_c.query_path) return _c.query_path_valid ? f.get_files(_c.query_path, "replace") : k("Invalid directory " + _c.query_path, _c.query_path, "replace");
      if (location.search) {
        var a = location.search.split("&")[0].replace("?", "");
        if (a && "debug" !== a && (-1 === a.indexOf("=") || a.indexOf("/") > -1)) {
          _c.query_path = decodeURIComponent(a);
          var b = !(_c.dirs[_c.query_path] || -1 !== a.indexOf("/") || !_c.dirs[""] || !_c.dirs[""].files) && _c.dirs[""].files[_c.query_path];
          return b && b.is_dir && i(_c.query_path, b), f.get_files(_c.query_path, "replace")
        }
      }
      f.get_files(_c.init_path, "replace")
    }, I(ag.topbar_info, function(a, b) {
      if ("reset" === a) return as.clear(!0)
    }), s(ag.files, function(b) {
      var c = b.target;
      if (c !== ag.files) {
        var d = c.closest(".files-a"),
          a = !!d && _c.files[d.dataset.name];
        if (a) return c.classList.contains("context-button") ? f.create_contextmenu(b, "files", c, a, d) : u.contextmenu.is_open && ("menu" !== _c.click || a.is_dir) ? b.preventDefault() : c.dataset.href ? (b.preventDefault(), window.open(c.dataset.href)) : !a.is_dir && ("window" === _c.click || a.ext && click_window.includes(a.ext)) ? _c.click_window_popup ? ae.popup(b, 1e3, null, d.href, a.basename) : void 0 : void((a.is_dir || "download" !== _c.click) && (Q(b, d) || (b.preventDefault(), a.is_dir ? (i(a.path, a), f.get_files(a.path, "push")) : "menu" === _c.click ? f.create_contextmenu(b, "files", d, a) : "popup" === _c.click && a.is_popup && a.is_readable ? f.open_popup(a) : f.open_modal(a, !0))))
      }
    }), history.scrollRestoration = "manual"
  }(),
  function() {
    var q = {
        list: {},
        imagelist: {},
        blocks: {
          contain: !0
        },
        grid: {
          contain: !0,
          size: {
            default: 160,
            min: 80,
            max: 240
          }
        },
        rows: {
          size: {
            default: 150,
            min: 80,
            max: 220
          }
        },
        columns: {
          size: {
            default: 180,
            min: 120,
            max: 240
          }
        }
      },
      i = Object.keys(q);
    i.includes(_c.layout) || (_c.layout = "rows"), ag.files.className != "list files-" + _c.layout && (ag.files.className = "list files-" + _c.layout);
    var k = getComputedStyle(ag.files).getPropertyValue("--img-object-fit").trim() || "cover",
      d = j.get("files:interface:img-object-fit") || k;

    function r() {
      ag.files.style.setProperty("--imagelist-height", g ? "100px" : "100%"), ag.files.style[(g ? "set" : "remove") + "Property"]("--imagelist-min-height", "auto")
    }
    d != k && ag.files.style.setProperty("--img-object-fit", d);
    var g = j.get("files:layout:imagelist-square");

    function t(a) {
      return {
        layout: a,
        ob: q[a],
        index: i.indexOf(a)
      }
    }
    null === g && (g = "auto" !== getComputedStyle(ag.files).getPropertyValue("--imagelist-height").trim()), r(), ["grid", "rows", "columns"].forEach(function(b) {
      var c, a, d = q[b].size,
        e = getComputedStyle(ag.files).getPropertyValue("--" + b + "-size");
      e && (d.default = parseInt(e)), d.current = !(c = j.get("files:layout:" + b + "-size")) || isNaN(c) || c == d.default ? d.default : (c = aa(d.min, d.max, c), ag.files.style.setProperty("--" + b + "-size", c + "px"), c), d.space = !(a = j.get("files:layout:" + b + "-space-factor")) || isNaN(a) || 50 == a ? 50 : (a = aa(0, 100, a), ag.files.style.setProperty("--" + b + "-space-factor", a), 0 == a && ag.files.style.setProperty("--" + b + "-border-radius", 0), a)
    });
    var a = t(_c.layout);

    function u() {
      var b = a.ob;
      c.style.display = "imagelist" === a.layout || b.size || b.contain ? "" : "none", h.style.display = b.size ? "" : "none", o.style.display = b.size ? "" : "none", w.style.display = b.contain ? "" : "none", y.style.display = "imagelist" === a.layout ? "" : "none", b.size && (b.size.min && (n.min = b.size.min), b.size.max && (n.max = b.size.max), b.size.default && (C.value = b.size.default, n.style.setProperty("--range-default-pos", (b.size.default-b.size.min) / (b.size.max - b.size.min))), aj.set(_, a.layout), n.value = b.size.current, aj.set(D, a.layout), p.value = b.size.space)
    }
    var b = _id("change-layout");
    b.innerHTML = '<button type="button" class="btn-icon btn-topbar">' + f.get_svg_icon("layout_" + a.layout) + '</button><div class="dropdown-menu dropdown-menu-topbar dropdown-menu-center"><h6 class="dropdown-header" data-lang="layout">' + aj.get("layout") + "</h6><div>" + V(i, function(b) {
      return '<button class="dropdown-item' + (b === a.layout ? " active" : "") + '" data-action="' + b + '">' + f.get_svg_icon("layout_" + b) + '<span class="dropdown-text" data-lang="' + b + '">' + aj.get(b) + "<span></button>"
    }) + '</div><div id="layout-options"><div id="layout-sizer"><label for="layout-sizer-range" class="form-label mb-0"><span data-lang="size">' + aj.get("size") + '</span><span data-lang="' + a.layout + '" class="layout-label-type">' + aj.get(a.layout) + '</span></label><input type="range" class="form-range" id="layout-sizer-range" value="200" min="100" max="300" list="layout-size-default"><datalist id="layout-size-default"><option value="200"></datalist></div><div id="layout-spacer"><label for="layout-spacer-range" class="form-label mb-0"><span data-lang="space">' + aj.get("space") + '</span><span data-lang="' + a.layout + '" class="layout-label-type">' + aj.get(a.layout) + '</span></label><input type="range" class="form-range" id="layout-spacer-range" value="50" min="0" max="100" list="layout-space-default"><datalist id="layout-space-default"><option value="50"></datalist></div><div id="cover-toggle"><div class="form-check"><input class="form-check-input" type="checkbox" id="covertoggle"' + ("cover" === d ? " checked" : "") + '><label class="form-check-label" for="covertoggle" data-lang="uniform">' + aj.get("uniform") + '</label></div></div><div id="imagelist-square"><div class="form-check"><input class="form-check-input" type="checkbox" id="imagelistsquare"' + (g ? " checked" : "") + '><label class="form-check-label" for="imagelistsquare" data-lang="uniform">' + aj.get("uniform") + "</label></div></div></div>";
    var v = b.firstElementChild,
      l = b.lastElementChild,
      m = l.children[1],
      B = m.children,
      c = l.children[2],
      h = c.firstElementChild,
      _ = h.firstElementChild.lastElementChild,
      n = h.children[1],
      C = h.children[2].lastElementChild,
      o = c.children[1],
      D = o.firstElementChild.lastElementChild,
      p = o.children[1],
      w = c.children[2],
      x = w.firstElementChild.firstElementChild,
      y = c.children[3],
      z = y.firstElementChild.firstElementChild;
    u();
    var E = e.is_pointer ? 200 : 100;

    function A(b) {
      a.layout !== b && (v.innerHTML = f.get_svg_icon("layout_" + b), B[a.index].classList.remove("active"), B[(a = t(b)).index].classList.add("active"), u(), ag.files.className = "list files-" + b, ag.sortbar.className = "sortbar-" + b, f.set_config("layout", b))
    }
    s(n, function(b) {
      _c.files_count <= E && ag.files.style.setProperty("--" + a.layout + "-size", n.value + "px")
    }, "input"), s(n, function(b) {
      _c.files_count > E && ag.files.style.setProperty("--" + a.layout + "-size", n.value + "px"), a.ob.size.current = n.value, j.set("files:layout:" + a.layout + "-size", n.value)
    }, "change"), s(p, function(b) {
      _c.files_count <= E && ag.files.style.setProperty("--" + a.layout + "-space-factor", p.value)
    }, "input"), s(p, function(c) {
      var b = a.ob.size.space = p.value;
      _c.files_count > E && ag.files.style.setProperty("--" + a.layout + "-space-factor", b), ag.files.style[(b > 0 ? "remove" : "set") + "Property"]("--" + a.layout + "-border-radius", 0), j.set("files:layout:" + a.layout + "-space-factor", b)
    }, "change"), s(x, function() {
      (d = this.checked ? "cover" : "contain") == k ? (ag.files.style.removeProperty("--img-object-fit"), j.remove("files:interface:img-object-fit")) : (ag.files.style.setProperty("--img-object-fit", d), j.set("files:interface:img-object-fit", d))
    }, "change"), s(z, function() {
      g = this.checked, r(), j.set("files:layout:imagelist-square", g)
    }, "change"), I(m, A), f.dropdown(b, v, function() {
      A(i[a.index >= i.length - 1 ? 0 : a.index + 1])
    })
  }(),
  function() {
    var d, g, h, a = u.popup = {
        transitions: {
          glide: function(a) {
            return {
              translateX: [10 * a, 0],
              opacity: [.1, 1],
              duration: 500,
              easing: "easeOutQuart"
            }
          },
          fade: function(a) {
            return {
              opacity: [.1, 1],
              duration: 400,
              easing: "easeOutCubic"
            }
          },
          zoom: function(a) {
            return {
              scale: [1.05, 1],
              opacity: [.1, 1],
              duration: 500,
              easing: "easeOutQuint"
            }
          },
          pop: function(a) {
            return {
              scale: {
                value: [.9, 1],
                duration: 600,
                easing: "easeOutElastic"
              },
              opacity: {
                value: [0, 1],
                duration: 400,
                easing: "easeOutCubic"
              },
              duration: 600
            }
          },
          elastic: function(a) {
            return {
              translateX: {
                value: [50 * a, 0],
                duration: 600,
                easing: "easeOutElastic"
              },
              opacity: {
                value: [.1, 1],
                duration: 500,
                easing: "easeOutQuart"
              },
              duration: 600
            }
          },
          wipe: function(a) {
            return {
              translateX: [10 * a, 0],
              opacity: [.1, 1],
              clipPath: [a > 0 ? "inset(0% 25% 0% 65%)" : "inset(0% 65% 0% 25%)", "inset(0% 0% 0% 0%)"],
              scale: [1.05, 1],
              duration: 500,
              easing: "easeOutQuint"
            }
          }
        },
        playing: !1,
        timer: !1
      },
      c = j.get("files:popup:locked_caption"),
      i = screen.width < 375 ? "ll" : screen.width < 414 ? "lll" : "llll",
      k = screen.width >= 576,
      b = {
        caption_hide: !0,
        caption_style: "block",
        caption_align: "center-left",
        click: "prev_next",
        downloadEl: !e.is_pointer,
        mapEl: !1,
        play_interval: 5e3,
        getDoubleTapZoom: function() {
          return a.toggle_play(!1), 1
        },
        getThumbBoundsFn: function(m, h) {
          var b = d.items[m],
            a = u.modal.open ? u.modal.item === b.item && _class("modal-image", ag.modal)[0] : b.img_el;
          if (!(b.w && b.h && b.msrc && a && a.offsetParent)) return !!h && l(!0);
          var c = a.getBoundingClientRect();
          if (h) {
            if (c.bottom < 0 || c.top > window.innerHeight) return l(!0);
            l(!1)
          }
          var e = b.w / b.h,
            i = c.width / c.height,
            j = "cover" === getComputedStyle(a).objectFit ? e < i : e > i,
            n = j ? (a.clientWidth / e - a.clientHeight) / 2 : 0,
            k = j ? 0 : (a.clientHeight * e - a.clientWidth) / 2,
            f = a.offsetWidth - a.clientWidth,
            g = parseFloat(getComputedStyle(a).padding || getComputedStyle(a).paddingTop || 0);
          return {
            x: c.left - k + f / 2 + g,
            y: c.top - n + window.pageYOffset + f / 2 + g,
            w: c.width + 2 * k - f - 2 * g
          }
        },
        index: 0,
        addCaptionHTMLFn: function(d, h) {
          var c = d.item;
          if ("topbar" === b.caption_style) return a.search.innerHTML = c.basename, !1;
          if (!ag.filter.value) {
            if ("video" === d.type) return a.search.innerHTML = c.basename, G(a.caption_center);
            G(a.search)
          }
          return c.hasOwnProperty("popup_caption") || (c.popup_caption = M(e.PointerEvent, "popup-context") + '<div class="popup-basename">' + z(c.basename) + "</div>" + K(c.dimensions, "popup-dimensions") + L(c, "popup-filesize") + '<span class="popup-date">' + f.get_time(c, i, "LLLL", k) + "</span>" + N(c.image, "popup-exif") + _(c.image, "popup")), c.popup_caption ? (a.caption_transition_delay && (a.caption.style.cssText = "transition: none; opacity: 0", g && clearTimeout(g), g = setTimeout(function() {
            a.caption.style.cssText = "transition: opacity 333ms cubic-bezier(0.33, 1, 0.68, 1)", g = setTimeout(function() {
              a.caption.removeAttribute("style")
            }, 333)
          }, a.caption_transition_delay)), a.caption_center.innerHTML = c.popup_caption, !0) : aw.resetEl(a.caption_center)
        }
      };

    function l(b) {
      b !== d.options.showHideOpacity && (d.options.showHideOpacity = b, aw.toggle_class(a.pswp, "pswp--animate_opacity", b))
    }

    function m(c) {
      var b = !!(a.current_video && a.is_open && d && d.itemHolders) && a.current_video.firstElementChild;
      b && "VIDEO" == b.nodeName && b[c]()
    }

    function n(b) {
      m("pause"), a.current_video = b
    }

    function o() {
      return !d.options.loop && d.getCurrentIndex() === d.options.getNumItemsFn() - 1
    }

    function p() {
      a.pano_viewer && (a.pano_viewer.destroy(), a.pano_viewer = !1)
    }
    _c.config && _c.config.popup && (Object.assign(b, _c.config.popup), b.play_transition || (b.play_transition = b.transition || "glide"), b.transitions && Object.assign(a.transitions, b.transitions)), f.open_popup = function(i, j) {
      if (i && u.list.items.length && !a.is_open) {
        var c = {
          index: 0
        };
        if (h === u.list.matchingItems) {
          for (var g = 0; g < a.slides.length; g++)
            if (a.slides[g].item === i) {
              c.index = g;
              break
            }
        } else h = u.list.matchingItems, a.slides = [], h.forEach(function(h, m) {
          var b = h._values;
          if (b && b.is_readable && b.is_popup) {
            var d = {
              pid: encodeURIComponent(b.basename),
              item: b
            };
            if (al && b.is_pano) Object.assign(d, {
              type: "pano",
              html: '<div class="popup-pano-placeholder">' + f.get_svg_icon("panorama_variant") + "</div>"
            });
            else if (b.browser_image) {
              var g = !!_c.load_images && h.elm.firstElementChild,
                j = !e.image_orientation && Z(b.image),
                k = g && !j;
              if (Object.assign(d, {
                  type: "image",
                  src: D(b),
                  w: b.image ? b.image.width : screen.availHeight,
                  h: b.image ? b.image.height : screen.availHeight,
                  img_el: g,
                  msrc: !(!k || !g.complete) && g.getAttribute("src")
                }), j && (d.w = b.image.height, d.h = b.image.width), k && !d.msrc && (g.onload = function() {
                  d.msrc = this.getAttribute("src")
                }), "ico" === b.ext && d.w <= 16) {
                var l = 256 / d.w;
                d.w *= l, d.h *= l
              }
            } else b.is_browser_video && Object.assign(d, {
              type: "video",
              html: '<video class="popup-video" playsinline disablepictureinpicture controls controlsList="nodownload"><source src="' + D(b) + '" type="' + b.mime + '"></video>'
            });
            b === i && (c.index = a.slides.length), a.slides.push(d)
          }
        });
        a.slides.length && (ab(), document.documentElement.classList.add("popup-open"), a.is_open = !0, a.caption_transition_delay = 333, a.container.style.cursor = a.slides.length > 1 ? "pointer" : "default", "topbar" !== b.caption_style && ag.filter.value && (a.search.innerHTML = f.get_svg_icon("image_search_outline") + '"' + z(ag.filter.value) + '"'), a.slides.length < 3 && (c.playEl = !1), d = new ax(a.pswp, av, a.slides, Object.assign({}, b, c, {
          arrowEl: a.slides.length > 1 && (!e.only_touch || _c.current_dir.has_pano),
          arrowKeys: a.slides.length > 1,
          counterEl: a.slides.length > 1,
          showAnimationDuration: j ? 0 : 333,
          showHideOpacity: !a.slides[c.index].msrc && !j
        })), e.is_touch && d.listen("zoomGestureEnded", function() {
          d.getZoomLevel() > d.currItem.initialZoomLevel && a.toggle_play(!1)
        }), d.listen("beforeChange", function() {
          n("video" == d.currItem.type && d.currItem.container), a.toggle_timer(!1)
        }), d.listen("afterChange", function() {
          var b, c = d.currItem.type;
          if (a.toggle_timer(!0), ["video", "pano"].forEach(b => a.ui.classList.toggle("popup-ui-" + b, c == b)), !!(b = "pano" == c) != !!a.pano_viewer && Object.assign(d.options, {
              pinchToClose: !b,
              closeOnScroll: !b,
              closeOnVerticalDrag: !b,
              arrowKeys: !b && a.slides.length > 1
            }), p(), "pano" == c) {
            var e = d.currItem;
            f.load_plugin("pannellum", () => {
              e === d.currItem && (a.pano_viewer = pannellum.viewer(a.pano_container, {
                type: "equirectangular",
                panorama: e.item.is_pano,
                autoLoad: !0,
                autoRotate: a.pano_is_rotating ? -2 : 0,
                autoRotateInactivityDelay: 3e3,
                showControls: !1,
                hfov: window.innerWidth > window.innerHeight ? 105 : 75
              }))
            })
          }
        }), i.is_browser_video && _c.video_autoplay && d.listen("initialZoomInEnd", function() {
          m("play")
        }), d.listen("imageLoadComplete", function(b, c) {
          d.options.playEl && b === d.getCurrentIndex() && a.toggle_timer(!0)
        }), d.listen("close", function() {
          n(!1), a.toggle_play(!1)
        }), d.listen("destroy", function() {
          document.documentElement.classList.remove("popup-open"), a.preloader.classList.remove("svg-preloader-active");
          for (var b = 0; b < a.items.length; b++) G(a.items[b]);
          p(), G(a.search), a.is_open = !1
        }), d.init())
      }
    }, a.toggle_play = function(b) {
      b === a.playing || b && o() || (a.playing = !!b, aw.toggle_class(a.play_button, "is-playing", b), a.toggle_timer(b))
    }, a.toggle_timer = function(b) {
      if (b && o()) return a.toggle_play(!1);
      if ((!b || a.playing && (d.currItem.loaded || !d.currItem.src)) && a.timer != b) {
        a.timer = !!b, b && (a.play_timer.style.opacity = 1), anime.remove(a.play_timer);
        var c = {
          targets: a.play_timer,
          duration: b ? d.options.play_interval : 333,
          easing: b ? "easeInOutCubic" : "easeOutQuad",
          scaleX: b ? [0, 1] : 0
        };
        b ? (c.begin = function() {
          a.play_timer.style.display = "block"
        }, c.complete = function() {
          d.next(!0)
        }) : (c.complete = function() {
          a.play_timer.style.display = "none"
        }, c.opacity = [1, 0]), anime(c)
      }
    }, a.pano_is_rotating = !1 !== j.get("files:popup:pano:rotating"), document.body.insertAdjacentHTML("beforeend", '		<div class="pswp' + (e.is_touch ? " pswp--touch" : "") + (e.only_pointer ? " pswp--has_mouse" : "") + '" tabindex="-1" role="dialog" aria-hidden="true">	    	<div class="pswp__bg"></div>	    	<div class="pswp__scroll-wrap">	    		<div class="pswp__container' + (_c.server_exif ? " server-exif" : "") + '">		        <div class="pswp__item"></div>		        <div class="pswp__item"></div>		        <div class="pswp__item"></div>	        </div>	        <div class="pswp__ui pswp__ui--hidden pswp__caption-align-' + b.caption_align + '">	          <div class="pswp__top-bar">	            <div class="pswp__counter"></div>	            <div class="pswp__search"></div>	            <div class="pswp__topbar-spacer"></div>	            <svg viewBox="0 0 18 18" class="pswp__preloader svg-preloader"><circle cx="9" cy="9" r="8" pathLength="100" class="svg-preloader-circle"></svg>' + (b.downloadEl ? '<a type="button" class="pswp__button pswp__button--download"' + O(e.download ? "download" : "open in new tab") + ' target="_blank"' + (e.download ? " download" : "") + ">" + f.get_svg_icon(e.download ? "download" : "open_in_new") + "</a>" : M(!0, "pswp__button pswp__button--contextmenu")) + '							<button class="pswp__button pswp__button--pano-rotate' + (a.pano_is_rotating ? " is-rotating" : "") + '">' + f.get_svg_icon_multi("motion_play_outline", "motion_pause_outline") + "</button>" + (e.only_touch ? "" : '<button class="pswp__button pswp__button--zoom">' + f.get_svg_icon_multi("zoom_in", "zoom_out") + "</button>") + '	            <button class="pswp__button pswp__button--play">' + f.get_svg_icon_multi("play", "pause") + "</button>	            " + (e.fullscreen ? '<button class="pswp__button pswp__button--fs">' + f.get_svg_icon_multi("expand", "collapse") + "</button>" : "") + '	            <button class="pswp__button pswp__button--close">' + f.get_svg_icon("close") + '</button>	          </div>	          <button class="pswp__button pswp__button--arrow--left">' + f.get_svg_icon("chevron_left") + '</button><button class="pswp__button pswp__button--arrow--right">' + f.get_svg_icon("chevron_right") + '</button>	          <div class="pswp__timer"></div>	          <div class="pswp__caption pswp__caption-style-' + b.caption_style + (b.caption_hide ? " pswp__caption-hide" : "") + (c ? " pswp__caption-locked" : "") + '">	          	' + (e.only_touch ? "" : '<button class="pswp__button pswp__button--lock-caption">' + f.get_svg_icon_multi("lock_outline", "lock_open_outline") + "</button>") + '	          	<div class="pswp__caption__center"></div>	          </div>	        </div>	    	</div>				<div class="pswp__pano"></div>			</div>'), a.pswp = document.body.lastElementChild, a.bg = a.pswp.firstElementChild, a.scrollwrap = a.pswp.children[1], a.pano_container = a.pswp.lastElementChild, a.container = a.scrollwrap.firstElementChild, a.items = a.container.children, a.ui = a.scrollwrap.lastElementChild, a.topbar = a.ui.firstElementChild, a.caption = a.ui.lastElementChild, a.caption_center = a.caption.lastElementChild, a.play_timer = a.ui.children[3], Array.from(a.topbar.children).forEach(function(b) {
      var c = b.classList;
      return c.contains("pswp__preloader") ? a.preloader = b : c.contains("pswp__button--play") ? a.play_button = b : c.contains("pswp__search") ? a.search = b : c.contains("pswp__button--contextmenu") ? a.contextmenu_button = b : c.contains("pswp__button--pano-rotate") ? a.pano_rotate_button = b : void 0
    }), a.toggle_pano_rotate = () => {
      if (a.pano_viewer) {
        if (a.pano_is_rotating = !a.pano_is_rotating, a.pano_viewer[(a.pano_is_rotating ? "start" : "stop") + "AutoRotate"](), a.pano_is_rotating) {
          var b = a.pano_viewer.getConfig();
          b.autoRotate = -2, b.autoRotateInactivityDelay = 3e3
        }
        j.set("files:popup:pano:rotating", !!a.pano_is_rotating), a.pano_rotate_button.classList.toggle("is-rotating", a.pano_is_rotating)
      }
    }, a.caption.addEventListener("click", function(g) {
      return g.target.classList.contains("pswp__button--lock-caption") ? (c = !c, aw.toggle_class(a.caption, "pswp__caption-locked", c), j.set("files:popup:locked_caption", c, !0)) : "context" == g.target.dataset.action ? f.create_contextmenu(g, "popup", g.target, d.currItem.item) : void(e.is_pointer && 0 === g.target.className.indexOf("pswp") && ("right" === b.caption_align && g.pageX > this.clientWidth - 49 ? d.next() : "left" === b.caption_align && g.pageX < 49 && d.prev()))
    }), e.is_pointer && a.contextmenu_button && s(a.contextmenu_button, function(a) {
      f.create_contextmenu(a, "popup", a.target, d.currItem.item)
    })
  }(),
  function() {
    var a = document.body;

    function b(b) {
      a.dataset.updated = b, a.style.cursor = "pointer";
      var c = S(a, "click", function() {
        a.classList.remove("updated"), a.removeAttribute("data-updated"), a.style.removeProperty("cursor"), c.remove()
      })
    }
    _c.check_updates && (j.get("files:updated") ? (j.remove("files:updated"), b("\xe2\u0153\u201C Successfully updated to Files app version " + _c.version), a.classList.add("updated")) : Y({
      json_response: !0,
      params: "action=check_updates",
      complete: function(c, h, i) {
        if (c && h && i && c.hasOwnProperty("success")) {
          var d = c.success;
          if (o(d ? "New version " + d + " available." : "Already using latest version " + _c.version), d) {
            _id("change-sort").insertAdjacentHTML("afterend", '<div id="files-notifications" class="dropdown"><button type="button" class="btn-icon btn-topbar">' + f.get_svg_icon("bell") + '</button><div class="dropdown-menu dropdown-menu-topbar"><h6 class="dropdown-header">Files ' + d + "</h6>" + (c.writeable ? '<button class="dropdown-item">' + f.get_svg_icon("rotate_right") + '<span class="dropdown-text" data-lang="update">' + aj.get("update") + "</span></button>" : "") + (e.download ? '<a href="https://cdn.jsdelivr.net/npm/files.photo.gallery@' + d + '/index.php" class="dropdown-item" download>' + f.get_svg_icon("download") + '<span class="dropdown-text" data-lang="download">' + aj.get("download") + "</span></a>" : "") + '<a href="https://files.photo.gallery/latest" class="dropdown-item" target="_blank">' + f.get_svg_icon("info") + '<span class="dropdown-text" data-lang="read more">' + aj.get("read more") + "</span></a></div></div>");
            var g = _id("files-notifications");
            if (f.dropdown(g, g.firstChild), !c.writeable) return;
            s(g.children[1].children[1], function() {
              ap.fire("Update to Files app " + d + "?").then(c => {
                c.isConfirmed && (a.classList.add("updating"), Y({
                  params: "action=do_update&version=" + d,
                  json_response: !0,
                  complete: function(c, d, e) {
                    if (a.classList.add("updated"), a.classList.remove("updating"), c && d && e && c.hasOwnProperty("success") && c.success) {
                      j.set("files:updated", !0);
                      try {
                        a.dataset.updated = "\xe2\u0153\u201C Success! Reloading ...", location.reload(!0)
                      } catch (f) {
                        a.dataset.updated = "\xe2\u0153\u201C Success! Please refresh ..."
                      }
                    } else b("\xe2\u0153\u2014 Failed to load update :(")
                  }
                }))
              })
            })
          }
        } else o("Failed to load external JSON check_updates.")
      }
    }))
  }(), window.onpopstate = function(a) {
    _c.history && a.state && a.state.hasOwnProperty("path") && f.get_files(a.state.path)
  };
  var au = {
    123: "application/vnd.lotus-1-2-3",
    ez: "application/andrew-inset",
    aw: "application/applixware",
    atom: "application/atom+xml",
    atomcat: "application/atomcat+xml",
    atomdeleted: "application/atomdeleted+xml",
    atomsvc: "application/atomsvc+xml",
    dwd: "application/atsc-dwd+xml",
    held: "application/atsc-held+xml",
    rsat: "application/atsc-rsat+xml",
    bdoc: "application/x-bdoc",
    xcs: "application/calendar+xml",
    ccxml: "application/ccxml+xml",
    cdfx: "application/cdfx+xml",
    cdmia: "application/cdmi-capability",
    cdmic: "application/cdmi-container",
    cdmid: "application/cdmi-domain",
    cdmio: "application/cdmi-object",
    cdmiq: "application/cdmi-queue",
    cu: "application/cu-seeme",
    mpd: "application/dash+xml",
    davmount: "application/davmount+xml",
    dbk: "application/docbook+xml",
    dssc: "application/dssc+der",
    xdssc: "application/dssc+xml",
    ecma: "application/ecmascript",
    es: "application/ecmascript",
    emma: "application/emma+xml",
    emotionml: "application/emotionml+xml",
    epub: "application/epub+zip",
    exi: "application/exi",
    fdt: "application/fdt+xml",
    pfr: "application/font-tdpfr",
    geojson: "application/geo+json",
    gml: "application/gml+xml",
    gpx: "application/gpx+xml",
    gxf: "application/gxf",
    gz: "application/gzip",
    hjson: "application/hjson",
    stk: "application/hyperstudio",
    ink: "application/inkml+xml",
    inkml: "application/inkml+xml",
    ipfix: "application/ipfix",
    its: "application/its+xml",
    jar: "application/java-archive",
    war: "application/java-archive",
    ear: "application/java-archive",
    ser: "application/java-serialized-object",
    class: "application/java-vm",
    js: "application/javascript",
    mjs: "application/javascript",
    json: "application/json",
    map: "application/json",
    json5: "application/json5",
    jsonml: "application/jsonml+json",
    jsonld: "application/ld+json",
    lgr: "application/lgr+xml",
    lostxml: "application/lost+xml",
    hqx: "application/mac-binhex40",
    cpt: "application/mac-compactpro",
    mads: "application/mads+xml",
    webmanifest: "application/manifest+json",
    mrc: "application/marc",
    mrcx: "application/marcxml+xml",
    ma: "application/mathematica",
    nb: "application/mathematica",
    mb: "application/mathematica",
    mathml: "application/mathml+xml",
    mbox: "application/mbox",
    mscml: "application/mediaservercontrol+xml",
    metalink: "application/metalink+xml",
    meta4: "application/metalink4+xml",
    mets: "application/mets+xml",
    maei: "application/mmt-aei+xml",
    musd: "application/mmt-usd+xml",
    mods: "application/mods+xml",
    m21: "application/mp21",
    mp21: "application/mp21",
    mp4s: "application/mp4",
    m4p: "application/mp4",
    xdf: "application/xcap-diff+xml",
    doc: "application/msword",
    dot: "application/msword",
    mxf: "application/mxf",
    nq: "application/n-quads",
    nt: "application/n-triples",
    cjs: "application/node",
    bin: "application/octet-stream",
    dms: "application/octet-stream",
    lrf: "application/octet-stream",
    mar: "application/octet-stream",
    so: "application/octet-stream",
    dist: "application/octet-stream",
    distz: "application/octet-stream",
    pkg: "application/octet-stream",
    bpk: "application/octet-stream",
    dump: "application/octet-stream",
    elc: "application/octet-stream",
    deploy: "application/octet-stream",
    exe: "application/x-msdownload",
    dll: "application/x-msdownload",
    deb: "application/x-debian-package",
    dmg: "application/x-apple-diskimage",
    iso: "application/x-iso9660-image",
    img: "application/octet-stream",
    msi: "application/x-msdownload",
    msp: "application/octet-stream",
    msm: "application/octet-stream",
    buffer: "application/octet-stream",
    oda: "application/oda",
    opf: "application/oebps-package+xml",
    ogx: "application/ogg",
    omdoc: "application/omdoc+xml",
    onetoc: "application/onenote",
    onetoc2: "application/onenote",
    onetmp: "application/onenote",
    onepkg: "application/onenote",
    oxps: "application/oxps",
    relo: "application/p2p-overlay+xml",
    xer: "application/xcap-error+xml",
    pdf: "application/pdf",
    pgp: "application/pgp-encrypted",
    asc: "application/pgp-signature",
    sig: "application/pgp-signature",
    prf: "application/pics-rules",
    p10: "application/pkcs10",
    p7m: "application/pkcs7-mime",
    p7c: "application/pkcs7-mime",
    p7s: "application/pkcs7-signature",
    p8: "application/pkcs8",
    ac: "application/vnd.nokia.n-gage.ac+xml",
    cer: "application/pkix-cert",
    crl: "application/pkix-crl",
    pkipath: "application/pkix-pkipath",
    pki: "application/pkixcmp",
    pls: "application/pls+xml",
    ai: "application/postscript",
    eps: "application/postscript",
    ps: "application/postscript",
    provx: "application/provenance+xml",
    cww: "application/prs.cww",
    pskcxml: "application/pskc+xml",
    raml: "application/raml+yaml",
    rdf: "application/rdf+xml",
    owl: "application/rdf+xml",
    rif: "application/reginfo+xml",
    rnc: "application/relax-ng-compact-syntax",
    rl: "application/resource-lists+xml",
    rld: "application/resource-lists-diff+xml",
    rs: "application/rls-services+xml",
    rapd: "application/route-apd+xml",
    sls: "application/route-s-tsid+xml",
    rusd: "application/route-usd+xml",
    gbr: "application/rpki-ghostbusters",
    mft: "application/rpki-manifest",
    roa: "application/rpki-roa",
    rsd: "application/rsd+xml",
    rss: "application/rss+xml",
    rtf: "text/rtf",
    sbml: "application/sbml+xml",
    scq: "application/scvp-cv-request",
    scs: "application/scvp-cv-response",
    spq: "application/scvp-vp-request",
    spp: "application/scvp-vp-response",
    sdp: "application/sdp",
    senmlx: "application/senml+xml",
    sensmlx: "application/sensml+xml",
    setpay: "application/set-payment-initiation",
    setreg: "application/set-registration-initiation",
    shf: "application/shf+xml",
    siv: "application/sieve",
    sieve: "application/sieve",
    smi: "application/smil+xml",
    smil: "application/smil+xml",
    rq: "application/sparql-query",
    srx: "application/sparql-results+xml",
    gram: "application/srgs",
    grxml: "application/srgs+xml",
    sru: "application/sru+xml",
    ssdl: "application/ssdl+xml",
    ssml: "application/ssml+xml",
    swidtag: "application/swid+xml",
    tei: "application/tei+xml",
    teicorpus: "application/tei+xml",
    tfi: "application/thraud+xml",
    tsd: "application/timestamped-data",
    toml: "application/toml",
    ttml: "application/ttml+xml",
    rsheet: "application/urc-ressheet+xml",
    "1km": "application/vnd.1000minds.decision-model+xml",
    plb: "application/vnd.3gpp.pic-bw-large",
    psb: "application/vnd.3gpp.pic-bw-small",
    pvb: "application/vnd.3gpp.pic-bw-var",
    tcap: "application/vnd.3gpp2.tcap",
    pwn: "application/vnd.3m.post-it-notes",
    aso: "application/vnd.accpac.simply.aso",
    imp: "application/vnd.accpac.simply.imp",
    acu: "application/vnd.acucobol",
    atc: "application/vnd.acucorp",
    acutc: "application/vnd.acucorp",
    air: "application/vnd.adobe.air-application-installer-package+zip",
    fcdt: "application/vnd.adobe.formscentral.fcdt",
    fxp: "application/vnd.adobe.fxp",
    fxpl: "application/vnd.adobe.fxp",
    xdp: "application/vnd.adobe.xdp+xml",
    xfdf: "application/vnd.adobe.xfdf",
    ahead: "application/vnd.ahead.space",
    azf: "application/vnd.airzip.filesecure.azf",
    azs: "application/vnd.airzip.filesecure.azs",
    azw: "application/vnd.amazon.ebook",
    acc: "application/vnd.americandynamics.acc",
    ami: "application/vnd.amiga.ami",
    apk: "application/vnd.android.package-archive",
    cii: "application/vnd.anser-web-certificate-issue-initiation",
    fti: "application/vnd.anser-web-funds-transfer-initiation",
    atx: "application/vnd.antix.game-component",
    mpkg: "application/vnd.apple.installer+xml",
    keynote: "application/vnd.apple.keynote",
    m3u8: "application/vnd.apple.mpegurl",
    numbers: "application/vnd.apple.numbers",
    pages: "application/vnd.apple.pages",
    pkpass: "application/vnd.apple.pkpass",
    swi: "application/vnd.aristanetworks.swi",
    iota: "application/vnd.astraea-software.iota",
    aep: "application/vnd.audiograph",
    bmml: "application/vnd.balsamiq.bmml+xml",
    mpm: "application/vnd.blueice.multipass",
    bmi: "application/vnd.bmi",
    rep: "application/vnd.businessobjects",
    cdxml: "application/vnd.chemdraw+xml",
    mmd: "application/vnd.chipnuts.karaoke-mmd",
    cdy: "application/vnd.cinderella",
    csl: "application/vnd.citationstyles.style+xml",
    cla: "application/vnd.claymore",
    rp9: "application/vnd.cloanto.rp9",
    c4g: "application/vnd.clonk.c4group",
    c4d: "application/vnd.clonk.c4group",
    c4f: "application/vnd.clonk.c4group",
    c4p: "application/vnd.clonk.c4group",
    c4u: "application/vnd.clonk.c4group",
    c11amc: "application/vnd.cluetrust.cartomobile-config",
    c11amz: "application/vnd.cluetrust.cartomobile-config-pkg",
    csp: "application/vnd.commonspace",
    cdbcmsg: "application/vnd.contact.cmsg",
    cmc: "application/vnd.cosmocaller",
    clkx: "application/vnd.crick.clicker",
    clkk: "application/vnd.crick.clicker.keyboard",
    clkp: "application/vnd.crick.clicker.palette",
    clkt: "application/vnd.crick.clicker.template",
    clkw: "application/vnd.crick.clicker.wordbank",
    wbs: "application/vnd.criticaltools.wbs+xml",
    pml: "application/vnd.ctc-posml",
    ppd: "application/vnd.cups-ppd",
    car: "application/vnd.curl.car",
    pcurl: "application/vnd.curl.pcurl",
    dart: "application/vnd.dart",
    rdz: "application/vnd.data-vision.rdz",
    uvf: "application/vnd.dece.data",
    uvvf: "application/vnd.dece.data",
    uvd: "application/vnd.dece.data",
    uvvd: "application/vnd.dece.data",
    uvt: "application/vnd.dece.ttml+xml",
    uvvt: "application/vnd.dece.ttml+xml",
    uvx: "application/vnd.dece.unspecified",
    uvvx: "application/vnd.dece.unspecified",
    uvz: "application/vnd.dece.zip",
    uvvz: "application/vnd.dece.zip",
    fe_launch: "application/vnd.denovo.fcselayout-link",
    dna: "application/vnd.dna",
    mlp: "application/vnd.dolby.mlp",
    dpg: "application/vnd.dpgraph",
    dfac: "application/vnd.dreamfactory",
    kpxx: "application/vnd.ds-keypoint",
    ait: "application/vnd.dvb.ait",
    svc: "application/vnd.dvb.service",
    geo: "application/vnd.dynageo",
    mag: "application/vnd.ecowin.chart",
    nml: "application/vnd.enliven",
    esf: "application/vnd.epson.esf",
    msf: "application/vnd.epson.msf",
    qam: "application/vnd.epson.quickanime",
    slt: "application/vnd.epson.salt",
    ssf: "application/vnd.epson.ssf",
    es3: "application/vnd.eszigno3+xml",
    et3: "application/vnd.eszigno3+xml",
    ez2: "application/vnd.ezpix-album",
    ez3: "application/vnd.ezpix-package",
    fdf: "application/vnd.fdf",
    mseed: "application/vnd.fdsn.mseed",
    seed: "application/vnd.fdsn.seed",
    dataless: "application/vnd.fdsn.seed",
    gph: "application/vnd.flographit",
    ftc: "application/vnd.fluxtime.clip",
    fm: "application/vnd.framemaker",
    frame: "application/vnd.framemaker",
    maker: "application/vnd.framemaker",
    book: "application/vnd.framemaker",
    fnc: "application/vnd.frogans.fnc",
    ltf: "application/vnd.frogans.ltf",
    fsc: "application/vnd.fsc.weblaunch",
    oas: "application/vnd.fujitsu.oasys",
    oa2: "application/vnd.fujitsu.oasys2",
    oa3: "application/vnd.fujitsu.oasys3",
    fg5: "application/vnd.fujitsu.oasysgp",
    bh2: "application/vnd.fujitsu.oasysprs",
    ddd: "application/vnd.fujixerox.ddd",
    xdw: "application/vnd.fujixerox.docuworks",
    xbd: "application/vnd.fujixerox.docuworks.binder",
    fzs: "application/vnd.fuzzysheet",
    txd: "application/vnd.genomatix.tuxedo",
    ggb: "application/vnd.geogebra.file",
    ggt: "application/vnd.geogebra.tool",
    gex: "application/vnd.geometry-explorer",
    gre: "application/vnd.geometry-explorer",
    gxt: "application/vnd.geonext",
    g2w: "application/vnd.geoplan",
    g3w: "application/vnd.geospace",
    gmx: "application/vnd.gmx",
    gdoc: "application/vnd.google-apps.document",
    gslides: "application/vnd.google-apps.presentation",
    gsheet: "application/vnd.google-apps.spreadsheet",
    kml: "application/vnd.google-earth.kml+xml",
    kmz: "application/vnd.google-earth.kmz",
    gqf: "application/vnd.grafeq",
    gqs: "application/vnd.grafeq",
    gac: "application/vnd.groove-account",
    ghf: "application/vnd.groove-help",
    gim: "application/vnd.groove-identity-message",
    grv: "application/vnd.groove-injector",
    gtm: "application/vnd.groove-tool-message",
    tpl: "application/vnd.groove-tool-template",
    vcg: "application/vnd.groove-vcard",
    hal: "application/vnd.hal+xml",
    zmm: "application/vnd.handheld-entertainment+xml",
    hbci: "application/vnd.hbci",
    les: "application/vnd.hhe.lesson-player",
    hpgl: "application/vnd.hp-hpgl",
    hpid: "application/vnd.hp-hpid",
    hps: "application/vnd.hp-hps",
    jlt: "application/vnd.hp-jlyt",
    pcl: "application/vnd.hp-pcl",
    pclxl: "application/vnd.hp-pclxl",
    "sfd-hdstx": "application/vnd.hydrostatix.sof-data",
    mpy: "application/vnd.ibm.minipay",
    afp: "application/vnd.ibm.modcap",
    listafp: "application/vnd.ibm.modcap",
    list3820: "application/vnd.ibm.modcap",
    irm: "application/vnd.ibm.rights-management",
    sc: "application/vnd.ibm.secure-container",
    icc: "application/vnd.iccprofile",
    icm: "application/vnd.iccprofile",
    igl: "application/vnd.igloader",
    ivp: "application/vnd.immervision-ivp",
    ivu: "application/vnd.immervision-ivu",
    igm: "application/vnd.insors.igm",
    xpw: "application/vnd.intercon.formnet",
    xpx: "application/vnd.intercon.formnet",
    i2g: "application/vnd.intergeo",
    qbo: "application/vnd.intu.qbo",
    qfx: "application/vnd.intu.qfx",
    rcprofile: "application/vnd.ipunplugged.rcprofile",
    irp: "application/vnd.irepository.package+xml",
    xpr: "application/vnd.is-xpr",
    fcs: "application/vnd.isac.fcs",
    jam: "application/vnd.jam",
    rms: "application/vnd.jcp.javame.midlet-rms",
    jisp: "application/vnd.jisp",
    joda: "application/vnd.joost.joda-archive",
    ktz: "application/vnd.kahootz",
    ktr: "application/vnd.kahootz",
    karbon: "application/vnd.kde.karbon",
    chrt: "application/vnd.kde.kchart",
    kfo: "application/vnd.kde.kformula",
    flw: "application/vnd.kde.kivio",
    kon: "application/vnd.kde.kontour",
    kpr: "application/vnd.kde.kpresenter",
    kpt: "application/vnd.kde.kpresenter",
    ksp: "application/vnd.kde.kspread",
    kwd: "application/vnd.kde.kword",
    kwt: "application/vnd.kde.kword",
    htke: "application/vnd.kenameaapp",
    kia: "application/vnd.kidspiration",
    kne: "application/vnd.kinar",
    knp: "application/vnd.kinar",
    skp: "application/vnd.koan",
    skd: "application/vnd.koan",
    skt: "application/vnd.koan",
    skm: "application/vnd.koan",
    sse: "application/vnd.kodak-descriptor",
    lasxml: "application/vnd.las.las+xml",
    lbd: "application/vnd.llamagraphics.life-balance.desktop",
    lbe: "application/vnd.llamagraphics.life-balance.exchange+xml",
    apr: "application/vnd.lotus-approach",
    pre: "application/vnd.lotus-freelance",
    nsf: "application/vnd.lotus-notes",
    org: "text/x-org",
    scm: "application/vnd.lotus-screencam",
    lwp: "application/vnd.lotus-wordpro",
    portpkg: "application/vnd.macports.portpkg",
    mcd: "application/vnd.mcd",
    mc1: "application/vnd.medcalcdata",
    cdkey: "application/vnd.mediastation.cdkey",
    mwf: "application/vnd.mfer",
    mfm: "application/vnd.mfmp",
    flo: "application/vnd.micrografx.flo",
    igx: "application/vnd.micrografx.igx",
    mif: "application/vnd.mif",
    daf: "application/vnd.mobius.daf",
    dis: "application/vnd.mobius.dis",
    mbk: "application/vnd.mobius.mbk",
    mqy: "application/vnd.mobius.mqy",
    msl: "application/vnd.mobius.msl",
    plc: "application/vnd.mobius.plc",
    txf: "application/vnd.mobius.txf",
    mpn: "application/vnd.mophun.application",
    mpc: "application/vnd.mophun.certificate",
    xul: "application/vnd.mozilla.xul+xml",
    cil: "application/vnd.ms-artgalry",
    cab: "application/vnd.ms-cab-compressed",
    xls: "application/vnd.ms-excel",
    xlm: "application/vnd.ms-excel",
    xla: "application/vnd.ms-excel",
    xlc: "application/vnd.ms-excel",
    xlt: "application/vnd.ms-excel",
    xlw: "application/vnd.ms-excel",
    xlam: "application/vnd.ms-excel.addin.macroenabled.12",
    xlsb: "application/vnd.ms-excel.sheet.binary.macroenabled.12",
    xlsm: "application/vnd.ms-excel.sheet.macroenabled.12",
    xltm: "application/vnd.ms-excel.template.macroenabled.12",
    eot: "application/vnd.ms-fontobject",
    chm: "application/vnd.ms-htmlhelp",
    ims: "application/vnd.ms-ims",
    lrm: "application/vnd.ms-lrm",
    thmx: "application/vnd.ms-officetheme",
    msg: "application/vnd.ms-outlook",
    cat: "application/vnd.ms-pki.seccat",
    stl: "model/stl",
    ppt: "application/vnd.ms-powerpoint",
    pps: "application/vnd.ms-powerpoint",
    pot: "application/vnd.ms-powerpoint",
    ppam: "application/vnd.ms-powerpoint.addin.macroenabled.12",
    pptm: "application/vnd.ms-powerpoint.presentation.macroenabled.12",
    sldm: "application/vnd.ms-powerpoint.slide.macroenabled.12",
    ppsm: "application/vnd.ms-powerpoint.slideshow.macroenabled.12",
    potm: "application/vnd.ms-powerpoint.template.macroenabled.12",
    mpp: "application/vnd.ms-project",
    mpt: "application/vnd.ms-project",
    docm: "application/vnd.ms-word.document.macroenabled.12",
    dotm: "application/vnd.ms-word.template.macroenabled.12",
    wps: "application/vnd.ms-works",
    wks: "application/vnd.ms-works",
    wcm: "application/vnd.ms-works",
    wdb: "application/vnd.ms-works",
    wpl: "application/vnd.ms-wpl",
    xps: "application/vnd.ms-xpsdocument",
    mseq: "application/vnd.mseq",
    mus: "application/vnd.musician",
    msty: "application/vnd.muvee.style",
    taglet: "application/vnd.mynfc",
    nlu: "application/vnd.neurolanguage.nlu",
    ntf: "application/vnd.nitf",
    nitf: "application/vnd.nitf",
    nnd: "application/vnd.noblenet-directory",
    nns: "application/vnd.noblenet-sealer",
    nnw: "application/vnd.noblenet-web",
    ngdat: "application/vnd.nokia.n-gage.data",
    "n-gage": "application/vnd.nokia.n-gage.symbian.install",
    rpst: "application/vnd.nokia.radio-preset",
    rpss: "application/vnd.nokia.radio-presets",
    edm: "application/vnd.novadigm.edm",
    edx: "application/vnd.novadigm.edx",
    ext: "application/vnd.novadigm.ext",
    odc: "application/vnd.oasis.opendocument.chart",
    otc: "application/vnd.oasis.opendocument.chart-template",
    odb: "application/vnd.oasis.opendocument.database",
    odf: "application/vnd.oasis.opendocument.formula",
    odft: "application/vnd.oasis.opendocument.formula-template",
    odg: "application/vnd.oasis.opendocument.graphics",
    otg: "application/vnd.oasis.opendocument.graphics-template",
    odi: "application/vnd.oasis.opendocument.image",
    oti: "application/vnd.oasis.opendocument.image-template",
    odp: "application/vnd.oasis.opendocument.presentation",
    otp: "application/vnd.oasis.opendocument.presentation-template",
    ods: "application/vnd.oasis.opendocument.spreadsheet",
    ots: "application/vnd.oasis.opendocument.spreadsheet-template",
    odt: "application/vnd.oasis.opendocument.text",
    odm: "application/vnd.oasis.opendocument.text-master",
    ott: "application/vnd.oasis.opendocument.text-template",
    oth: "application/vnd.oasis.opendocument.text-web",
    xo: "application/vnd.olpc-sugar",
    dd2: "application/vnd.oma.dd2+xml",
    obgx: "application/vnd.openblox.game+xml",
    oxt: "application/vnd.openofficeorg.extension",
    osm: "application/vnd.openstreetmap.data+xml",
    pptx: "application/vnd.openxmlformats-officedocument.presentationml.presentation",
    sldx: "application/vnd.openxmlformats-officedocument.presentationml.slide",
    ppsx: "application/vnd.openxmlformats-officedocument.presentationml.slideshow",
    potx: "application/vnd.openxmlformats-officedocument.presentationml.template",
    xlsx: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    xltx: "application/vnd.openxmlformats-officedocument.spreadsheetml.template",
    docx: "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    dotx: "application/vnd.openxmlformats-officedocument.wordprocessingml.template",
    mgp: "application/vnd.osgeo.mapguide.package",
    dp: "application/vnd.osgi.dp",
    esa: "application/vnd.osgi.subsystem",
    pdb: "application/x-pilot",
    pqa: "application/vnd.palm",
    oprc: "application/vnd.palm",
    paw: "application/vnd.pawaafile",
    str: "application/vnd.pg.format",
    ei6: "application/vnd.pg.osasli",
    efif: "application/vnd.picsel",
    wg: "application/vnd.pmi.widget",
    plf: "application/vnd.pocketlearn",
    pbd: "application/vnd.powerbuilder6",
    box: "application/vnd.previewsystems.box",
    mgz: "application/vnd.proteus.magazine",
    qps: "application/vnd.publishare-delta-tree",
    ptid: "application/vnd.pvi.ptid1",
    qxd: "application/vnd.quark.quarkxpress",
    qxt: "application/vnd.quark.quarkxpress",
    qwd: "application/vnd.quark.quarkxpress",
    qwt: "application/vnd.quark.quarkxpress",
    qxl: "application/vnd.quark.quarkxpress",
    qxb: "application/vnd.quark.quarkxpress",
    bed: "application/vnd.realvnc.bed",
    mxl: "application/vnd.recordare.musicxml",
    musicxml: "application/vnd.recordare.musicxml+xml",
    cryptonote: "application/vnd.rig.cryptonote",
    cod: "application/vnd.rim.cod",
    rm: "application/vnd.rn-realmedia",
    rmvb: "application/vnd.rn-realmedia-vbr",
    link66: "application/vnd.route66.link66+xml",
    st: "application/vnd.sailingtracker.track",
    see: "application/vnd.seemail",
    sema: "application/vnd.sema",
    semd: "application/vnd.semd",
    semf: "application/vnd.semf",
    ifm: "application/vnd.shana.informed.formdata",
    itp: "application/vnd.shana.informed.formtemplate",
    iif: "application/vnd.shana.informed.interchange",
    ipk: "application/vnd.shana.informed.package",
    twd: "application/vnd.simtech-mindmapper",
    twds: "application/vnd.simtech-mindmapper",
    mmf: "application/vnd.smaf",
    teacher: "application/vnd.smart.teacher",
    fo: "application/vnd.software602.filler.form+xml",
    sdkm: "application/vnd.solent.sdkm+xml",
    sdkd: "application/vnd.solent.sdkm+xml",
    dxp: "application/vnd.spotfire.dxp",
    sfs: "application/vnd.spotfire.sfs",
    sdc: "application/vnd.stardivision.calc",
    sda: "application/vnd.stardivision.draw",
    sdd: "application/vnd.stardivision.impress",
    smf: "application/vnd.stardivision.math",
    sdw: "application/vnd.stardivision.writer",
    vor: "application/vnd.stardivision.writer",
    sgl: "application/vnd.stardivision.writer-global",
    smzip: "application/vnd.stepmania.package",
    sm: "application/vnd.stepmania.stepchart",
    wadl: "application/vnd.sun.wadl+xml",
    sxc: "application/vnd.sun.xml.calc",
    stc: "application/vnd.sun.xml.calc.template",
    sxd: "application/vnd.sun.xml.draw",
    std: "application/vnd.sun.xml.draw.template",
    sxi: "application/vnd.sun.xml.impress",
    sti: "application/vnd.sun.xml.impress.template",
    sxm: "application/vnd.sun.xml.math",
    sxw: "application/vnd.sun.xml.writer",
    sxg: "application/vnd.sun.xml.writer.global",
    stw: "application/vnd.sun.xml.writer.template",
    sus: "application/vnd.sus-calendar",
    susp: "application/vnd.sus-calendar",
    svd: "application/vnd.svd",
    sis: "application/vnd.symbian.install",
    sisx: "application/vnd.symbian.install",
    xsm: "application/vnd.syncml+xml",
    bdm: "application/vnd.syncml.dm+wbxml",
    xdm: "application/vnd.syncml.dm+xml",
    ddf: "application/vnd.syncml.dmddf+xml",
    tao: "application/vnd.tao.intent-module-archive",
    pcap: "application/vnd.tcpdump.pcap",
    cap: "application/vnd.tcpdump.pcap",
    dmp: "application/vnd.tcpdump.pcap",
    tmo: "application/vnd.tmobile-livetv",
    tpt: "application/vnd.trid.tpt",
    mxs: "application/vnd.triscape.mxs",
    tra: "application/vnd.trueapp",
    ufd: "application/vnd.ufdl",
    ufdl: "application/vnd.ufdl",
    utz: "application/vnd.uiq.theme",
    umj: "application/vnd.umajin",
    unityweb: "application/vnd.unity",
    uoml: "application/vnd.uoml+xml",
    vcx: "application/vnd.vcx",
    vsd: "application/vnd.visio",
    vst: "application/vnd.visio",
    vss: "application/vnd.visio",
    vsw: "application/vnd.visio",
    vis: "application/vnd.visionary",
    vsf: "application/vnd.vsf",
    wbxml: "application/vnd.wap.wbxml",
    wmlc: "application/vnd.wap.wmlc",
    wmlsc: "application/vnd.wap.wmlscriptc",
    wtb: "application/vnd.webturbo",
    nbp: "application/vnd.wolfram.player",
    wpd: "application/vnd.wordperfect",
    wqd: "application/vnd.wqd",
    stf: "application/vnd.wt.stf",
    xar: "application/vnd.xara",
    xfdl: "application/vnd.xfdl",
    hvd: "application/vnd.yamaha.hv-dic",
    hvs: "application/vnd.yamaha.hv-script",
    hvp: "application/vnd.yamaha.hv-voice",
    osf: "application/vnd.yamaha.openscoreformat",
    osfpvg: "application/vnd.yamaha.openscoreformat.osfpvg+xml",
    saf: "application/vnd.yamaha.smaf-audio",
    spf: "application/vnd.yamaha.smaf-phrase",
    cmp: "application/vnd.yellowriver-custom-menu",
    zir: "application/vnd.zul",
    zirz: "application/vnd.zul",
    zaz: "application/vnd.zzazz.deck+xml",
    vxml: "application/voicexml+xml",
    wasm: "application/wasm",
    wgt: "application/widget",
    hlp: "application/winhlp",
    wsdl: "application/wsdl+xml",
    wspolicy: "application/wspolicy+xml",
    "7z": "application/x-7z-compressed",
    abw: "application/x-abiword",
    ace: "application/x-ace-compressed",
    arj: "application/x-arj",
    aab: "application/x-authorware-bin",
    x32: "application/x-authorware-bin",
    u32: "application/x-authorware-bin",
    vox: "application/x-authorware-bin",
    aam: "application/x-authorware-map",
    aas: "application/x-authorware-seg",
    bcpio: "application/x-bcpio",
    torrent: "application/x-bittorrent",
    blb: "application/x-blorb",
    blorb: "application/x-blorb",
    bz: "application/x-bzip",
    bz2: "application/x-bzip2",
    boz: "application/x-bzip2",
    cbr: "application/x-cbr",
    cba: "application/x-cbr",
    cbt: "application/x-cbr",
    cbz: "application/x-cbr",
    cb7: "application/x-cbr",
    vcd: "application/x-cdlink",
    cfs: "application/x-cfs-compressed",
    chat: "application/x-chat",
    pgn: "application/x-chess-pgn",
    crx: "application/x-chrome-extension",
    cco: "application/x-cocoa",
    nsc: "application/x-conference",
    cpio: "application/x-cpio",
    csh: "application/x-csh",
    udeb: "application/x-debian-package",
    dgc: "application/x-dgc-compressed",
    dir: "application/x-director",
    dcr: "application/x-director",
    dxr: "application/x-director",
    cst: "application/x-director",
    cct: "application/x-director",
    cxt: "application/x-director",
    w3d: "application/x-director",
    fgd: "application/x-director",
    swa: "application/x-director",
    wad: "application/x-doom",
    ncx: "application/x-dtbncx+xml",
    dtb: "application/x-dtbook+xml",
    res: "application/x-dtbresource+xml",
    dvi: "application/x-dvi",
    evy: "application/x-envoy",
    eva: "application/x-eva",
    bdf: "application/x-font-bdf",
    gsf: "application/x-font-ghostscript",
    psf: "application/x-font-linux-psf",
    pcf: "application/x-font-pcf",
    snf: "application/x-font-snf",
    pfa: "application/x-font-type1",
    pfb: "application/x-font-type1",
    pfm: "application/x-font-type1",
    afm: "application/x-font-type1",
    arc: "application/x-freearc",
    spl: "application/x-futuresplash",
    gca: "application/x-gca-compressed",
    ulx: "application/x-glulx",
    gnumeric: "application/x-gnumeric",
    gramps: "application/x-gramps-xml",
    gtar: "application/x-gtar",
    hdf: "application/x-hdf",
    php: "application/x-httpd-php",
    install: "application/x-install-instructions",
    jardiff: "application/x-java-archive-diff",
    jnlp: "application/x-java-jnlp-file",
    kdbx: "application/x-keepass2",
    latex: "application/x-latex",
    luac: "application/x-lua-bytecode",
    lzh: "application/x-lzh-compressed",
    lha: "application/x-lzh-compressed",
    run: "application/x-makeself",
    mie: "application/x-mie",
    prc: "application/x-pilot",
    mobi: "application/x-mobipocket-ebook",
    application: "application/x-ms-application",
    lnk: "application/x-ms-shortcut",
    wmd: "application/x-ms-wmd",
    wmz: "application/x-msmetafile",
    xbap: "application/x-ms-xbap",
    mdb: "application/x-msaccess",
    obd: "application/x-msbinder",
    crd: "application/x-mscardfile",
    clp: "application/x-msclip",
    com: "application/x-msdownload",
    bat: "application/x-msdownload",
    mvb: "application/x-msmediaview",
    m13: "application/x-msmediaview",
    m14: "application/x-msmediaview",
    wmf: "image/wmf",
    emf: "image/emf",
    emz: "application/x-msmetafile",
    mny: "application/x-msmoney",
    pub: "application/x-mspublisher",
    scd: "application/x-msschedule",
    trm: "application/x-msterminal",
    wri: "application/x-mswrite",
    nc: "application/x-netcdf",
    cdf: "application/x-netcdf",
    pac: "application/x-ns-proxy-autoconfig",
    nzb: "application/x-nzb",
    pl: "application/x-perl",
    pm: "application/x-perl",
    p12: "application/x-pkcs12",
    pfx: "application/x-pkcs12",
    p7b: "application/x-pkcs7-certificates",
    spc: "application/x-pkcs7-certificates",
    p7r: "application/x-pkcs7-certreqresp",
    rar: "application/x-rar-compressed",
    rpm: "application/x-redhat-package-manager",
    ris: "application/x-research-info-systems",
    sea: "application/x-sea",
    sh: "application/x-sh",
    shar: "application/x-shar",
    swf: "application/x-shockwave-flash",
    xap: "application/x-silverlight-app",
    sql: "application/x-sql",
    sit: "application/x-stuffit",
    sitx: "application/x-stuffitx",
    srt: "application/x-subrip",
    sv4cpio: "application/x-sv4cpio",
    sv4crc: "application/x-sv4crc",
    t3: "application/x-t3vm-image",
    gam: "application/x-tads",
    tar: "application/x-tar",
    tcl: "application/x-tcl",
    tk: "application/x-tcl",
    tex: "application/x-tex",
    tfm: "application/x-tex-tfm",
    texinfo: "application/x-texinfo",
    texi: "application/x-texinfo",
    obj: "model/obj",
    ustar: "application/x-ustar",
    hdd: "application/x-virtualbox-hdd",
    ova: "application/x-virtualbox-ova",
    ovf: "application/x-virtualbox-ovf",
    vbox: "application/x-virtualbox-vbox",
    "vbox-extpack": "application/x-virtualbox-vbox-extpack",
    vdi: "application/x-virtualbox-vdi",
    vhd: "application/x-virtualbox-vhd",
    vmdk: "application/x-virtualbox-vmdk",
    src: "application/x-wais-source",
    webapp: "application/x-web-app-manifest+json",
    der: "application/x-x509-ca-cert",
    crt: "application/x-x509-ca-cert",
    pem: "application/x-x509-ca-cert",
    fig: "application/x-xfig",
    xlf: "application/xliff+xml",
    xpi: "application/x-xpinstall",
    xz: "application/x-xz",
    z1: "application/x-zmachine",
    z2: "application/x-zmachine",
    z3: "application/x-zmachine",
    z4: "application/x-zmachine",
    z5: "application/x-zmachine",
    z6: "application/x-zmachine",
    z7: "application/x-zmachine",
    z8: "application/x-zmachine",
    xaml: "application/xaml+xml",
    xav: "application/xcap-att+xml",
    xca: "application/xcap-caps+xml",
    xel: "application/xcap-el+xml",
    xns: "application/xcap-ns+xml",
    xenc: "application/xenc+xml",
    xhtml: "application/xhtml+xml",
    xht: "application/xhtml+xml",
    xml: "text/xml",
    xsl: "application/xml",
    xsd: "application/xml",
    rng: "application/xml",
    dtd: "application/xml-dtd",
    xop: "application/xop+xml",
    xpl: "application/xproc+xml",
    xslt: "application/xslt+xml",
    xspf: "application/xspf+xml",
    mxml: "application/xv+xml",
    xhvml: "application/xv+xml",
    xvml: "application/xv+xml",
    xvm: "application/xv+xml",
    yang: "application/yang",
    yin: "application/yin+xml",
    zip: "application/zip",
    "3gpp": "video/3gpp",
    adp: "audio/adpcm",
    au: "audio/basic",
    snd: "audio/basic",
    mid: "audio/midi",
    midi: "audio/midi",
    kar: "audio/midi",
    rmi: "audio/midi",
    mxmf: "audio/mobile-xmf",
    mp3: "audio/mpeg",
    m4a: "audio/x-m4a",
    mp4a: "audio/mp4",
    mpga: "audio/mpeg",
    mp2: "audio/mpeg",
    mp2a: "audio/mpeg",
    m2a: "audio/mpeg",
    m3a: "audio/mpeg",
    oga: "audio/ogg",
    ogg: "audio/ogg",
    spx: "audio/ogg",
    s3m: "audio/s3m",
    sil: "audio/silk",
    uva: "audio/vnd.dece.audio",
    uvva: "audio/vnd.dece.audio",
    eol: "audio/vnd.digital-winds",
    dra: "audio/vnd.dra",
    dts: "audio/vnd.dts",
    dtshd: "audio/vnd.dts.hd",
    lvp: "audio/vnd.lucent.voice",
    pya: "audio/vnd.ms-playready.media.pya",
    ecelp4800: "audio/vnd.nuera.ecelp4800",
    ecelp7470: "audio/vnd.nuera.ecelp7470",
    ecelp9600: "audio/vnd.nuera.ecelp9600",
    rip: "audio/vnd.rip",
    wav: "audio/x-wav",
    weba: "audio/webm",
    aac: "audio/x-aac",
    aif: "audio/x-aiff",
    aiff: "audio/x-aiff",
    aifc: "audio/x-aiff",
    caf: "audio/x-caf",
    flac: "audio/flac",
    mka: "audio/x-matroska",
    m3u: "audio/x-mpegurl",
    wax: "audio/x-ms-wax",
    wma: "audio/x-ms-wma",
    ram: "audio/x-pn-realaudio",
    ra: "audio/x-realaudio",
    rmp: "audio/x-pn-realaudio-plugin",
    xm: "audio/xm",
    cdx: "chemical/x-cdx",
    cif: "chemical/x-cif",
    cmdf: "chemical/x-cmdf",
    cml: "chemical/x-cml",
    csml: "chemical/x-csml",
    xyz: "chemical/x-xyz",
    ttc: "font/collection",
    otf: "font/otf",
    ttf: "font/ttf",
    woff: "font/woff",
    woff2: "font/woff2",
    exr: "image/aces",
    apng: "image/apng",
    bmp: "image/x-ms-bmp",
    cgm: "image/cgm",
    drle: "image/dicom-rle",
    fits: "image/fits",
    g3: "image/g3fax",
    gif: "image/gif",
    heic: "image/heic",
    heics: "image/heic-sequence",
    heif: "image/heif",
    heifs: "image/heif-sequence",
    hej2: "image/hej2k",
    hsj2: "image/hsj2",
    ief: "image/ief",
    jls: "image/jls",
    jp2: "image/jp2",
    jpg2: "image/jp2",
    jpeg: "image/jpeg",
    jpg: "image/jpeg",
    jpe: "image/jpeg",
    jph: "image/jph",
    jhc: "image/jphc",
    jpm: "video/jpm",
    jpx: "image/jpx",
    jpf: "image/jpx",
    jxr: "image/jxr",
    jxra: "image/jxra",
    jxrs: "image/jxrs",
    jxs: "image/jxs",
    jxsc: "image/jxsc",
    jxsi: "image/jxsi",
    jxss: "image/jxss",
    ktx: "image/ktx",
    png: "image/png",
    btif: "image/prs.btif",
    pti: "image/prs.pti",
    sgi: "image/sgi",
    svg: "image/svg+xml",
    svgz: "image/svg+xml",
    t38: "image/t38",
    tif: "image/tiff",
    tiff: "image/tiff",
    tfx: "image/tiff-fx",
    psd: "image/vnd.adobe.photoshop",
    azv: "image/vnd.airzip.accelerator.azv",
    uvi: "image/vnd.dece.graphic",
    uvvi: "image/vnd.dece.graphic",
    uvg: "image/vnd.dece.graphic",
    uvvg: "image/vnd.dece.graphic",
    djvu: "image/vnd.djvu",
    djv: "image/vnd.djvu",
    sub: "text/vnd.dvb.subtitle",
    dwg: "image/vnd.dwg",
    dxf: "image/vnd.dxf",
    fbs: "image/vnd.fastbidsheet",
    fpx: "image/vnd.fpx",
    fst: "image/vnd.fst",
    mmr: "image/vnd.fujixerox.edmics-mmr",
    rlc: "image/vnd.fujixerox.edmics-rlc",
    ico: "image/x-icon",
    dds: "image/vnd.ms-dds",
    mdi: "image/vnd.ms-modi",
    wdp: "image/vnd.ms-photo",
    npx: "image/vnd.net-fpx",
    tap: "image/vnd.tencent.tap",
    vtf: "image/vnd.valve.source.texture",
    wbmp: "image/vnd.wap.wbmp",
    xif: "image/vnd.xiff",
    pcx: "image/x-pcx",
    webp: "image/webp",
    "3ds": "image/x-3ds",
    ras: "image/x-cmu-raster",
    cmx: "image/x-cmx",
    fh: "image/x-freehand",
    fhc: "image/x-freehand",
    fh4: "image/x-freehand",
    fh5: "image/x-freehand",
    fh7: "image/x-freehand",
    jng: "image/x-jng",
    sid: "image/x-mrsid-image",
    pic: "image/x-pict",
    pct: "image/x-pict",
    pnm: "image/x-portable-anymap",
    pbm: "image/x-portable-bitmap",
    pgm: "image/x-portable-graymap",
    ppm: "image/x-portable-pixmap",
    rgb: "image/x-rgb",
    tga: "image/x-tga",
    xbm: "image/x-xbitmap",
    xpm: "image/x-xpixmap",
    xwd: "image/x-xwindowdump",
    "disposition-notification": "message/disposition-notification",
    u8msg: "message/global",
    u8dsn: "message/global-delivery-status",
    u8mdn: "message/global-disposition-notification",
    u8hdr: "message/global-headers",
    eml: "message/rfc822",
    mime: "message/rfc822",
    wsc: "message/vnd.wfa.wsc",
    "3mf": "model/3mf",
    gltf: "model/gltf+json",
    glb: "model/gltf-binary",
    igs: "model/iges",
    iges: "model/iges",
    msh: "model/mesh",
    mesh: "model/mesh",
    silo: "model/mesh",
    mtl: "model/mtl",
    dae: "model/vnd.collada+xml",
    dwf: "model/vnd.dwf",
    gdl: "model/vnd.gdl",
    gtw: "model/vnd.gtw",
    mts: "model/vnd.mts",
    ogex: "model/vnd.opengex",
    x_b: "model/vnd.parasolid.transmit.binary",
    x_t: "model/vnd.parasolid.transmit.text",
    usdz: "model/vnd.usdz+zip",
    bsp: "model/vnd.valve.source.compiled-map",
    vtu: "model/vnd.vtu",
    wrl: "model/vrml",
    vrml: "model/vrml",
    x3db: "model/x3d+fastinfoset",
    x3dbz: "model/x3d+binary",
    x3dv: "model/x3d-vrml",
    x3dvz: "model/x3d+vrml",
    x3d: "model/x3d+xml",
    x3dz: "model/x3d+xml",
    appcache: "text/cache-manifest",
    manifest: "text/cache-manifest",
    ics: "text/calendar",
    ifb: "text/calendar",
    coffee: "text/coffeescript",
    litcoffee: "text/coffeescript",
    css: "text/css",
    csv: "text/csv",
    html: "text/html",
    htm: "text/html",
    shtml: "text/html",
    jade: "text/jade",
    jsx: "text/jsx",
    less: "text/less",
    markdown: "text/markdown",
    md: "text/markdown",
    mml: "text/mathml",
    mdx: "text/mdx",
    n3: "text/n3",
    txt: "text/plain",
    text: "text/plain",
    conf: "text/plain",
    def: "text/plain",
    list: "text/plain",
    log: "text/plain",
    in: "text/plain",
    ini: "text/plain",
    dsc: "text/prs.lines.tag",
    rtx: "text/richtext",
    sgml: "text/sgml",
    sgm: "text/sgml",
    shex: "text/shex",
    slim: "text/slim",
    slm: "text/slim",
    stylus: "text/stylus",
    styl: "text/stylus",
    tsv: "text/tab-separated-values",
    t: "text/troff",
    tr: "text/troff",
    roff: "text/troff",
    man: "text/troff",
    me: "text/troff",
    ms: "text/troff",
    ttl: "text/turtle",
    uri: "text/uri-list",
    uris: "text/uri-list",
    urls: "text/uri-list",
    vcard: "text/vcard",
    curl: "text/vnd.curl",
    dcurl: "text/vnd.curl.dcurl",
    mcurl: "text/vnd.curl.mcurl",
    scurl: "text/vnd.curl.scurl",
    fly: "text/vnd.fly",
    flx: "text/vnd.fmi.flexstor",
    gv: "text/vnd.graphviz",
    "3dml": "text/vnd.in3d.3dml",
    spot: "text/vnd.in3d.spot",
    jad: "text/vnd.sun.j2me.app-descriptor",
    wml: "text/vnd.wap.wml",
    wmls: "text/vnd.wap.wmlscript",
    vtt: "text/vtt",
    s: "text/x-asm",
    asm: "text/x-asm",
    c: "text/x-c",
    cc: "text/x-c",
    cxx: "text/x-c",
    cpp: "text/x-c",
    h: "text/x-c",
    hh: "text/x-c",
    dic: "text/x-c",
    htc: "text/x-component",
    f: "text/x-fortran",
    for: "text/x-fortran",
    f77: "text/x-fortran",
    f90: "text/x-fortran",
    hbs: "text/x-handlebars-template",
    java: "text/x-java-source",
    lua: "text/x-lua",
    mkd: "text/x-markdown",
    nfo: "text/x-nfo",
    opml: "text/x-opml",
    p: "text/x-pascal",
    pas: "text/x-pascal",
    pde: "text/x-processing",
    sass: "text/x-sass",
    scss: "text/x-scss",
    etx: "text/x-setext",
    sfv: "text/x-sfv",
    ymp: "text/x-suse-ymp",
    uu: "text/x-uuencode",
    vcs: "text/x-vcalendar",
    vcf: "text/x-vcard",
    yaml: "text/yaml",
    yml: "text/yaml",
    "3gp": "video/3gpp",
    "3g2": "video/3gpp2",
    h261: "video/h261",
    h263: "video/h263",
    h264: "video/h264",
    jpgv: "video/jpeg",
    jpgm: "video/jpm",
    mj2: "video/mj2",
    mjp2: "video/mj2",
    ts: "video/mp2t",
    mp4: "video/mp4",
    mp4v: "video/mp4",
    mpg4: "video/mp4",
    mpeg: "video/mpeg",
    mpg: "video/mpeg",
    mpe: "video/mpeg",
    m1v: "video/mpeg",
    m2v: "video/mpeg",
    ogv: "video/ogg",
    qt: "video/quicktime",
    mov: "video/quicktime",
    uvh: "video/vnd.dece.hd",
    uvvh: "video/vnd.dece.hd",
    uvm: "video/vnd.dece.mobile",
    uvvm: "video/vnd.dece.mobile",
    uvp: "video/vnd.dece.pd",
    uvvp: "video/vnd.dece.pd",
    uvs: "video/vnd.dece.sd",
    uvvs: "video/vnd.dece.sd",
    uvv: "video/vnd.dece.video",
    uvvv: "video/vnd.dece.video",
    dvb: "video/vnd.dvb.file",
    fvt: "video/vnd.fvt",
    mxu: "video/vnd.mpegurl",
    m4u: "video/vnd.mpegurl",
    pyv: "video/vnd.ms-playready.media.pyv",
    uvu: "video/vnd.uvvu.mp4",
    uvvu: "video/vnd.uvvu.mp4",
    viv: "video/vnd.vivo",
    webm: "video/webm",
    f4v: "video/x-f4v",
    fli: "video/x-fli",
    flv: "video/x-flv",
    m4v: "video/x-m4v",
    mkv: "video/x-matroska",
    mk3d: "video/x-matroska",
    mks: "video/x-matroska",
    mng: "video/x-mng",
    asf: "video/x-ms-asf",
    asx: "video/x-ms-asf",
    vob: "video/x-ms-vob",
    wm: "video/x-ms-wm",
    wmv: "video/x-ms-wmv",
    wmx: "video/x-ms-wmx",
    wvx: "video/x-ms-wvx",
    avi: "video/x-msvideo",
    movie: "video/x-sgi-movie",
    smv: "video/x-smv",
    ice: "x-conference/x-cooltalk"
  };
  ! function() {
    var g = {};
    ag.modal.innerHTML = '<div class="modal-dialog" role="document">	  <div class="modal-content">	    <div class="modal-header">	      <h5 class="modal-title"></h5>	      <div class="modal-buttons">	      	<div class="modal-code-buttons" style="display: none">' + (_c.allow_text_edit ? '<button type="button" class="btn btn-1 is-icon" data-action="save" data-tooltip="' + aj.get("save") + '" data-lang="save">' + f.get_svg_icon("save_edit") + "</button>" : "") + '<button type="button" class="btn btn-1 is-icon" data-action="copy" data-tooltip="' + aj.get("copy text") + '" data-lang="copy text">' + f.get_svg_icon("clipboard") + '</button><button type="button" class="btn btn-1 is-icon" data-action="fullscreen">' + f.get_svg_icon_multi("expand", "collapse") + '</button></div><button class="btn btn-1 is-icon" data-action="close"' + O("close") + ">" + f.get_svg_icon("close") + '</button>	      </div>	    </div>	    <div class="modal-body"></div>	  </div>	</div>';
    var d = ag.modal.children[0],
      a = d.children[0],
      b = a.children[0],
      h = b.children[0],
      c = b.children[1].children[0],
      i = (c.lastElementChild, !!_c.allow_text_edit && c.children[0]),
      k = a.children[1];
    u.modal = {};
    var l = R(function() {
      u.modal.code_mirror && u.modal.code_mirror.refresh()
    }, 500);

    function m(a) {
      ag.modal.style.display = a ? "block" : "none", ag.modal_bg.style.display = a ? "block" : "none", document.body.classList[a ? "add" : "remove"]("modal-open")
    }

    function n(d, e, b, c) {
      var a = {
        targets: d,
        opacity: e,
        easing: "easeOutQuint",
        duration: 250
      };
      b && (a.scale = b), c && (a.complete = c), anime(a)
    }
    f.open_modal = function(b, v) {
      var i = "",
        j = !1;
      if (Object.assign(u.modal, {
          item: b,
          resize_listener: !1,
          type: b.is_dir ? "dir" : "file"
        }), !b.is_dir && b.is_readable) {
        if (b.browser_image) u.modal.type = "image", b.dimensions && b.dimensions[0] > 800 && b.ratio > 1 && document.documentElement.clientWidth >= 1600 && (j = !0), i = '<img data-action="zoom" src="' + D(b) + '" class="modal-image files-img-placeholder' + ("ico" == b.ext ? " modal-image-ico" : "") + '"' + (!b.dimensions || !_c.server_exif && e.image_orientation || !e.image_orientation && Z(b.image) ? "" : ' width="' + b.dimensions[0] + '" height="' + b.dimensions[1] + '" style="--ratio:' + b.ratio + '"') + "></img>";
        else if (b.is_browser_video) u.modal.type = "video", i = '<video src="' + D(b) + '" type="' + b.mime + '" class="modal-video" controls playsinline disablepictureinpicture controlslist="nodownload"' + (_c.video_autoplay ? " autoplay" : "") + "></video>";
        else if (_c.video_thumbs_enabled && "video" === b.mime0 && b.is_readable) u.modal.type = "video-thumb", i = '<img src="' + _c.script + "?file=" + encodeURIComponent(b.path) + "&resize=video&" + _c.image_cache_hash + "." + b.mtime + "." + b.filesize + '" class="modal-image files-img-placeholder" width="' + b.preview_dimensions[0] + '" height="' + b.preview_dimensions[1] + '" style="--ratio:' + b.preview_ratio + '"></img>';
        else if (ah("audio", b)) u.modal.type = "audio", i = f.get_svg_large(b, "modal-svg") + '<audio src="' + D(b) + '" type="' + b.mime + '" class="modal-audio" controls playsinline controlslist="nodownload"></audio>';
        else {
          if (!b.hasOwnProperty("code_mode")) {
            var o = function(a) {
              if (a && !(a.filesize > _c.code_max_load)) {
                if (a.ext && "htaccess" === a.ext) return CodeMirror.findModeByName("nginx");
                var b = !!a.mime && CodeMirror.findModeByMIME(a.mime);
                return b && "null" !== b.mode || !a.ext || (b = CodeMirror.findModeByExtension(a.ext) || b), b
              }
            }(b);
            b.code_mode = o && o.mode || !1
          }
          b.code_mode && (u.modal.type = "code", b.filesize > 1e3 && (j = !0), f.load_plugin("codemirror"), i = '<div class="spinner-border modal-preview-spinner"></div>' + f.get_svg_large(b, "modal-svg"))
        }
      }
      i || (i = f.get_svg_large(b, "modal-svg") + ad(b));
      var p = ["image", "file"].includes(u.modal.type) || "dir" === u.modal.type && b.url_path ? "a" : "div",
        w = "<" + ("a" === p ? 'a href="' + D(b) + '" target="_blank" title="' + aj.get("image" === u.modal.type ? "zoom" : "open in new tab") + '"' : "div") + ' class="modal-preview modal-preview-' + u.modal.type + '">' + i + "</" + p + '><div class="modal-info">' + M(!0, "modal-info-context") + '<div class="modal-info-name">' + z(b.basename) + '</div>			<div class="modal-info-meta">' + (b.mime ? '<span class="modal-info-mime">' + f.get_svg_icon_files(b) + b.mime + "</span>" : "") + K(b.dimensions, "modal-info-dimensions") + L(b, "modal-info-filesize") + (y = "modal-info-permissions", A = (x = b).is_readable && x.is_writeable, x.fileperms ? '<span class="' + y + (A ? " is-readwrite" : " not-readwrite") + '">' + f.get_svg_icon(A ? "lock_open_outline" : "lock_outline") + x.fileperms + "</span>" : "") + '</div>			<div class="modal-info-date">' + f.get_svg_icon("date") + f.get_time(b, "llll", "LLLL", !0) + "</div>" + N(b.image, "modal-info-exif") + _(b.image, "modal-info", !0) + "</div>";
      d.classList.toggle("modal-lg", j), a.classList.add("modal-content-" + u.modal.type), h.innerText = b.basename, e.is_pointer && (h.title = b.basename), k.innerHTML = w;
      var x, y, A, s, t = !!b.browser_image && _class("files-img-placeholder", k)[0];
      t && t.addEventListener("load", function() {
        this.classList.remove("files-img-placeholder")
      }), _c.history && (v && history.pushState(null, b.basename, "#" + encodeURIComponent(b.basename)), u.modal.popstate = S(window, "popstate", function() {
        f.close_modal()
      })), s = function() {
        if (b.code_mode) {
          var a = u.modal.open;
          g.file = Y({
            params: "action=file&file=" + encodeURIComponent(b.path),
            complete: function(d) {
              g.file = !1, f.load_plugin("codemirror", function() {
                if (u.modal.open === a) {
                  H(_class("modal-preview-spinner", k));
                  var e = _class("modal-preview-code", k)[0];
                  e && (H(_class("modal-svg", k)), u.modal.code_mirror = CodeMirror(e, {
                    value: d,
                    lineWrapping: !0,
                    lineNumbers: !0,
                    readOnly: !_c.allow_text_edit,
                    mode: b.code_mode,
                    viewportMargin: 1 / 0,
                    extraKeys: Object.assign({
                      F11: r,
                      Esc: r
                    }, _c.allow_text_edit ? {
                      "Ctrl-S": q,
                      "Cmd-S": q
                    } : {})
                  }), CodeMirror.autoLoadMode(u.modal.code_mirror, b.code_mode), u.modal.resize_listener = S(window, "resize", l), c.style.display = "")
                }
              })
            }
          })
        }
      }, u.modal.open = Math.random(), X("esc", f.close_modal, "keyup"), ab(), m(!0), n(ag.modal_bg, [0, .8]), n(a, [0, 1], [.98, 1], s)
    }, f.close_modal = function(b) {
      u.modal.open = !1, g.file && g.file.abort(), X("esc", "keyup"), u.modal.resize_listener && u.modal.resize_listener.remove(), n(ag.modal_bg, [.8, 0]), n(a, [1, 0], [1, .98], function() {
        ag.modal.scrollTop = 0, m(), G(k), G(h), ag.modal.classList.remove("modal-code-fullscreen"), a.classList.remove("modal-content-" + u.modal.type), u.modal.code_mirror = !1, "code" === u.modal.type && (c.style.display = "none")
      }), _c.history && u.modal.popstate && (u.modal.popstate.remove(), b && (history.state ? history.replaceState({
        path: _c.current_path
      }, _c.current_dir.basename || "/", location.pathname + location.search) : history.back()))
    };
    var o = !1;

    function p(a, b) {
      o = !1, i.classList.remove("tooltip-loading"), ac.timer(i, b, a ? "success" : "danger")
    }

    function q(a) {
      if (!o && !i.disabled) {
        if (_c.demo_mode) return v.fire("Demo mode");
        if (!al) return ao.fire();
        if (!u.modal.item.is_writeable) return v.fire("File is not writeable");
        o = !0, i.classList.add("tooltip-loading");
        var b = _c.current_dir;
        Y({
          params: "action=fm&task=text_edit&path=" + u.modal.item.path + "&text=" + encodeURIComponent(u.modal.code_mirror.getValue()),
          json_response: !0,
          fail: function() {
            p(!1, "fail"), v.fire()
          },
          complete: function(a) {
            p(a.success, a.error), a.success ? (an.fire(aj.get("save", !0) + " " + u.modal.item.basename), j.remove(at(b.path, b.path.mtime)), delete b.files, delete b.html) : v.fire(a.error)
          }
        })
      }
    }

    function r() {
      ag.modal.classList.toggle("modal-code-fullscreen"), l()
    }
    I(ag.modal, function(a, b) {
      if ("context" === a) f.create_contextmenu(b, "modal", b.target, u.modal.item);
      else if ("close" === a) f.close_modal(!0);
      else if ("zoom" === a) {
        if (u.contextmenu.is_open) return b.preventDefault();
        if (Q(b, b.target.closest(".modal-preview"))) return;
        u.modal.popstate.remove(), f.open_popup(u.modal.item)
      } else if ("copy" === a) {
        var c = u.modal.code_mirror.getValue(),
          d = c && P(c);
        ac.timer(b.target, !1, d ? "success" : "danger")
      } else "fullscreen" === a ? r() : "save" === a && q()
    })
  }();
  var av = function(b, c) {
      var d, f, g, h, i, j, k, l, m, n, o, p, q, r, _, s, t, a = this,
        v = !1,
        w = !0,
        x = {
          timeToIdle: 3e3,
          timeToIdleOutside: 1e3,
          loadingIndicatorDelay: 1e3,
          addCaptionHTMLFn: function(a, b) {
            return a.title ? b.firstElementChild.innerHTML = a.title : c.resetEl(b.firstElementChild)
          },
          closeEl: !0,
          captionEl: !0,
          fullscreenEl: e.fullscreen,
          zoomEl: !0,
          downloadEl: !1,
          mapEl: !0,
          playEl: !0,
          panoRotateEl: !0,
          counterEl: !0,
          arrowEl: !0,
          preloaderEl: !0,
          closeOnOutsideClick: !0,
          tapToClose: !1,
          clickToCloseNonZoomable: !1,
          clickToShowNextNonZoomable: !1,
          indexIndicatorSep: " / ",
          fitControlsWidth: 1200
        },
        y = function(a) {
          if (_) return !0;
          a = a || window.event, r.timeToIdle && r.mouseUsed && !l && D();
          for (var b, d, e = a.target || a.srcElement, c = 0; c < G.length; c++)(b = G[c]).onTap && e.classList.contains("pswp__" + b.name) && (b.onTap(), d = !0);
          d && (a.stopPropagation(), _ = !0, setTimeout(function() {
            _ = !1
          }, 30))
        },
        z = function(b) {
          32 === b.keyCode && (e.is_dual_input && a.toggleControls(!1), a.setIdle(!l))
        },
        A = function(a, b, c) {
          a.classList[c ? "add" : "remove"]("pswp__" + b)
        },
        B = function() {
          var a = 1 === r.getNumItemsFn();
          a !== q && (A(d, "ui--one-slide", a), q = a)
        },
        C = 0,
        D = function() {
          clearTimeout(t), C = 0, l && a.setIdle(!1)
        },
        E = function(b) {
          var c = (b = b || window.event).relatedTarget || b.toElement;
          c && "HTML" !== c.nodeName || (clearTimeout(t), t = setTimeout(function() {
            a.setIdle(!0)
          }, r.timeToIdleOutside))
        },
        F = function(a) {
          o !== a && (c.toggle_class(n, "svg-preloader-active", !a), o = a)
        },
        G = [{
          name: "caption",
          option: "captionEl",
          onInit: function(a) {
            f = a
          }
        }, {
          name: "button--download",
          option: "downloadEl",
          onInit: function(a) {
            j = a
          },
          onTap: function() {}
        }, {
          name: "button--map",
          option: "mapEl",
          onInit: function(a) {
            k = a
          },
          onTap: function() {}
        }, {
          name: "button--zoom",
          option: "zoomEl",
          onTap: b.toggleDesktopZoom
        }, {
          name: "counter",
          option: "counterEl",
          onInit: function(a) {
            g = a
          }
        }, {
          name: "button--close",
          option: "closeEl",
          onTap: b.close
        }, {
          name: "button--arrow--left",
          option: "arrowEl",
          onInit: function(a) {
            h = a
          },
          onTap: function() {
            b.prev()
          }
        }, {
          name: "button--arrow--right",
          option: "arrowEl",
          onInit: function(a) {
            i = a
          },
          onTap: function() {
            b.next()
          }
        }, {
          name: "button--fs",
          option: "fullscreenEl",
          onInit: function(a) {},
          onTap: function() {
            screenfull.toggle()
          }
        }, {
          name: "preloader",
          option: "preloaderEl",
          onInit: function(a) {
            n = a
          }
        }, {
          name: "button--play",
          option: "playEl",
          onTap: function() {
            u.popup.toggle_play(!u.popup.playing)
          }
        }, {
          name: "button--pano-rotate",
          option: "panoRotateEl",
          onTap: u.popup.toggle_pano_rotate
        }];
      a.init = function() {
        var g, h, i, e;
        c.copy_unique(b.options, x), r = b.options, d = u.popup.ui, (m = b.listen)("onVerticalDrag", function(b) {
          w && b < .95 ? a.toggleControls() : !w && b >= .95 && a.toggleControls(!0)
        }), m("onPinchClose", function(b) {
          w && b < .9 ? (a.toggleControls(), g = !0) : g && !w && b > .9 && a.toggleControls(!0)
        }), m("zoomGestureEnded", function() {
          g = !1
        }), m("beforeChange", a.update), r.downloadEl && m("afterChange", function() {
          var a = b.currItem.original || b.currItem.src;
          j.setAttribute("href", a || "#"), j.style.display = a ? "" : "none"
        }), r.mapEl && m("afterChange", function() {
          var a = b.currItem.item,
            c = !!(a && a.image && a.image.exif) && a.image.exif.gps;
          k.style.display = c ? "" : "none", k.setAttribute("href", c ? J(c) : "#")
        }), m("doubleTap", function(c) {
          var a = b.currItem.initialZoomLevel;
          b.zoomTo(b.getZoomLevel() === a ? r.getDoubleTapZoom(!1, b.currItem) : a, c, 250)
        }), m("preventDragEvent", function(b, d, c) {
          var a = b.target || b.srcElement;
          a && a.getAttribute("class") && b.type.indexOf("mouse") > -1 && (a.getAttribute("class").indexOf("__caption") > 0 || /(SMALL|STRONG|EM)/i.test(a.tagName)) && (c.prevent = !1, (void 0)())
        }), m("bindEvents", function() {
          c.bind(d, "pswpTap click", y), c.bind(u.popup.scrollwrap, "pswpTap", a.onGlobalTap), c.bind(document, "keydown", z)
        }), m("unbindEvents", function() {
          s && clearInterval(s), c.unbind(document, "mouseout", E), c.unbind(document, "mousemove", D), c.unbind(d, "pswpTap click", y), c.unbind(u.popup.scrollwrap, "pswpTap", a.onGlobalTap), c.unbind(document, "keydown", z)
        }), m("destroy", function() {
          r.captionEl && f.classList.remove("pswp__caption--empty"), d.classList.add("pswp__ui--hidden"), t && clearTimeout(t), a.setIdle(!1)
        }), r.showAnimationDuration || d.classList.remove("pswp__ui--hidden"), m("initialZoomIn", function() {
          r.showAnimationDuration && d.classList.remove("pswp__ui--hidden")
        }), m("initialZoomOut", function() {
          d.classList.add("pswp__ui--hidden")
        }), (e = function(a) {
          if (a)
            for (var d = a.length, b = 0; b < d; b++) {
              h = a[b];
              for (var c = 0; c < G.length; c++) i = G[c], h.classList.contains("pswp__" + i.name) && (r[i.option] ? (h.classList.remove("pswp__element--disabled"), i.onInit && i.onInit(h)) : h.classList.add("pswp__element--disabled"))
            }
        })(d.children), e(u.popup.topbar.children), B(), r.timeToIdle && m("mouseUsed", function() {
          c.bind(document, "mousemove", D), c.bind(document, "mouseout", E), s = setInterval(function() {
            2 == ++C && a.setIdle(!0)
          }, r.timeToIdle / 2)
        }), r.preloaderEl && (F(!0), m("beforeChange", function() {
          clearTimeout(p), p = setTimeout(function() {
            b.currItem && b.currItem.loading ? b.currItem.img && !b.currItem.img.naturalWidth && F(!1) : F(!0)
          }, r.loadingIndicatorDelay)
        }), m("imageLoadComplete", function(c, a) {
          b.currItem === a && F(!0)
        }))
      }, a.setIdle = function(a) {
        l = a, A(d, "ui--idle", a)
      }, a.update = function() {
        if (w && b.currItem) {
          if (a.updateIndexIndicator(), r.captionEl) {
            var c = r.addCaptionHTMLFn(b.currItem, f);
            A(f, "caption--empty", !c)
          }
          v = !0
        } else v = !1;
        B()
      }, a.updateIndexIndicator = function() {
        r.counterEl && (g.innerHTML = b.getCurrentIndex() + 1 + r.indexIndicatorSep + r.getNumItemsFn()), !r.loop && r.arrowEl && r.getNumItemsFn() > 1 && (c.toggle_class(h, "pswp__element--disabled", 0 === b.getCurrentIndex()), c.toggle_class(i, "pswp__element--disabled", b.getCurrentIndex() === r.getNumItemsFn() - 1))
      }, a.onGlobalTap = function(c) {
        var d = (c = c || window.event).target || c.srcElement;
        if (!_) {
          if (c.detail && "mouse" === c.detail.pointerType) {
            if (!c.detail.rightClick) {
              if (("zoom" == r.click || 2 > r.getNumItemsFn() || b.getZoomLevel() > b.currItem.fitRatio) && d.classList.contains("pswp__img")) b.currItem.fitRatio < 1 && b.toggleDesktopZoom(c.detail.releasePoint);
              else if (0 === d.className.indexOf("pswp__")) {
                var f = (r.getNumItemsFn() > 2 || !b.getCurrentIndex()) && ("next" == r.click || c.detail.releasePoint.x > u.popup.pswp.clientWidth / 2) ? "next" : "prev";
                b[f]()
              }
            }
          } else e.is_dual_input ? e.legacy_ie || a.setIdle(!l) : a.toggleControls(!w)
        }
      }, a.toggleControls = function(b) {
        w = b, b && !v && a.update(), A(d, "ui--hidden", !b)
      }
    },
    aw = {
      bind: function(c, a, d, e) {
        var f = (e ? "remove" : "add") + "EventListener";
        a = a.split(" ");
        for (var b = 0; b < a.length; b++) a[b] && c[f](a[b], d, !1)
      },
      createEl: function(a, c) {
        var b = document.createElement(c || "div");
        return a && (b.className = a), b
      },
      resetEl: function(a) {
        for (; a.firstChild;) a.removeChild(a.firstChild)
      },
      getScrollY: function() {
        return window.pageYOffset
      },
      unbind: function(a, b, c) {
        aw.bind(a, b, c, !0)
      },
      toggle_class: function(a, b, c) {
        a.classList[c ? "add" : "remove"](b)
      },
      arraySearch: function(b, c, d) {
        for (var a = b.length; a--;)
          if (b[a][d] === c) return a;
        return -1
      },
      copy_unique: function(b, a) {
        Object.keys(a).forEach(function(c) {
          b.hasOwnProperty(c) || (b[c] = a[c])
        })
      },
      easing: {
        sine: {
          out: function(a) {
            return Math.sin(a * (Math.PI / 2))
          },
          inOut: function(a) {
            return -(Math.cos(Math.PI * a) - 1) / 2
          }
        },
        cubic: {
          out: function(a) {
            return --a * a * a + 1
          }
        }
      },
      features: {
        touch: e.is_touch,
        raf: window.requestAnimationFrame,
        caf: window.cancelAnimationFrame,
        pointerEvent: !!window.PointerEvent || navigator.msPointerEnabled,
        is_mouse: e.only_pointer
      }
    },
    ax = function(i, j, k, c) {
      var d = this,
        b = {
          allowPanToNext: !0,
          spacing: .12,
          bgOpacity: 1,
          mouseUsed: e.only_pointer,
          loop: !0,
          pinchToClose: !0,
          closeOnScroll: !0,
          closeOnVerticalDrag: !0,
          verticalDragRange: .75,
          hideAnimationDuration: 333,
          showAnimationDuration: 333,
          showHideOpacity: !1,
          focus: !0,
          escKey: !0,
          arrowKeys: !0,
          mainScrollEndFriction: .35,
          panEndFriction: .35,
          transition: "glide",
          play_transition: "glide",
          isClickableElement: function(a) {
            return "A" === a.tagName
          },
          getDoubleTapZoom: function(a, b) {
            return a || b.initialZoomLevel < .7 ? 1 : 1.33
          },
          maxSpreadZoom: 1
        };
      Object.assign(b, c);
      var l, m, n, o, p, q, r, s, t, v, w, _, x, y, z, A, B, C, D, E, F, G, H, I, J, K, L, M, N, O, P, Q, R, S, T, U, V, W, X, Y, Z, aa, ab, ac, ad, $, ae, af, ag, ah, ai, aj = {
          x: 0,
          y: 0
        },
        ak = {
          x: 0,
          y: 0
        },
        al = {
          x: 0,
          y: 0
        },
        f = {},
        am = 0,
        an = {},
        ao = {
          x: 0,
          y: 0
        },
        ap = 0,
        aq = [],
        ar = !1,
        a = function(a, b) {
          Object.assign(d, b.publicMethods), aq.push(a)
        },
        as = function(a) {
          var b = bA();
          return a > b - 1 ? a - b : a < 0 ? b + a : a
        },
        at = {},
        g = function(a, b) {
          return at[a] || (at[a] = []), at[a].push(b)
        },
        h = function(e) {
          var a = at[e];
          if (a) {
            var c = Array.prototype.slice.call(arguments);
            c.shift();
            for (var b = 0; b < a.length; b++) a[b].apply(d, c)
          }
        },
        au = function() {
          return (new Date).getTime()
        },
        av = function(a) {
          ag = a, u.popup.bg.style.opacity = a * b.bgOpacity
        },
        ax = function(c, e, f, b, a) {
          (!ar || a && a !== d.currItem) && (b /= a ? a.fitRatio : d.currItem.fitRatio), c.transform = _ + e + "px, " + f + "px, 0px) scale(" + b + ")"
        },
        ay = function(a) {
          ac && !d.currItem.loadError && (a && (v > d.currItem.fitRatio ? ar || (bJ(d.currItem, !1, !0), ar = !0) : ar && (bJ(d.currItem), ar = !1)), ax(ac, al.x, al.y, v))
        },
        az = function(a) {
          a.container && ax(a.container.style, a.initialPosition.x, a.initialPosition.y, a.initialZoomLevel, a)
        },
        aA = function(a, b) {
          b.transform = _ + a + "px, 0px, 0px)"
        },
        aB = function(a, e) {
          if (!b.loop && e) {
            var d = o + (ao.x * am - a) / ao.x,
              c = Math.round(a - a5.x);
            (d < 0 && c > 0 || d >= bA() - 1 && c < 0) && (a = a5.x + c * b.mainScrollEndFriction)
          }
          a5.x = a, aA(a, p)
        },
        aC = function(a, c) {
          var b = a6[a] - an[a];
          return ak[a] + aj[a] + b - b * (c / w)
        },
        aD = function(b, a) {
          b.x = a.x, b.y = a.y, a.id && (b.id = a.id)
        },
        aE = function(a) {
          a.x = Math.round(a.x), a.y = Math.round(a.y)
        },
        aF = function() {
          L.is_mouse || (i.classList.add("pswp--has_mouse"), I += " pswp--has_mouse", L.is_mouse = b.mouseUsed = !0), h("mouseUsed")
        },
        aG = null,
        aH = function() {
          aG && (aw.unbind(document, "mousemove", aH), aF()), aG = setTimeout(function() {
            aG = null
          }, 100)
        },
        aI = function(b, c) {
          var a = bF(d.currItem, f, b);
          return c && (ab = a), a
        },
        aJ = function(a) {
          return (a || d.currItem).initialZoomLevel
        },
        aK = function(a) {
          return (a || d.currItem).w > 0 ? b.maxSpreadZoom : 1
        },
        aL = function(a, c, b, e) {
          return e === d.currItem.initialZoomLevel ? (b[a] = d.currItem.initialPosition[a], !0) : (b[a] = aC(a, e), b[a] > c.min[a] ? (b[a] = c.min[a], !0) : b[a] < c.max[a] && (b[a] = c.max[a], !0))
        },
        aM = function(a) {
          var c = "";
          if (b.escKey && 27 === a.keyCode ? c = "close" : b.arrowKeys && (37 === a.keyCode ? c = "prev" : 39 === a.keyCode && (c = "next")), !c || a.ctrlKey || a.altKey || a.shiftKey || a.metaKey) return !1;
          a.preventDefault(), d[c]()
        },
        aN = function(a) {
          a && (V || U || ad || R) && (a.preventDefault(), a.stopPropagation())
        },
        aO = function() {
          d.setScrollOffset(0, aw.getScrollY())
        },
        aP = {},
        aQ = 0,
        aR = function(a) {
          aP[a] && (aP[a].raf && H(aP[a].raf), aQ--, delete aP[a])
        },
        aS = function(a) {
          aP[a] && aR(a), aP[a] || (aQ++, aP[a] = {})
        },
        aT = function() {
          for (var a in aP) aP.hasOwnProperty(a) && aR(a)
        },
        aU = function(a, c, d, e, f, g, h) {
          var i, j = au();
          aS(a);
          var b = function() {
            if (aP[a]) {
              if ((i = au() - j) >= e) return aR(a), g(d), void(h && h());
              g((d - c) * f(i / e) + c), aP[a].raf = G(b)
            }
          };
          b()
        },
        aV = {},
        aW = {},
        aX = {},
        aY = {},
        aZ = {},
        a$ = [],
        a_ = {},
        a0 = [],
        a1 = {},
        a2 = 0,
        a3 = {
          x: 0,
          y: 0
        },
        a4 = 0,
        a5 = {
          x: 0,
          y: 0
        },
        a6 = {
          x: 0,
          y: 0
        },
        a7 = {
          x: 0,
          y: 0
        },
        a8 = function(a, b) {
          return a1.x = Math.abs(a.x - b.x), a1.y = Math.abs(a.y - b.y), Math.sqrt(a1.x * a1.x + a1.y * a1.y)
        },
        a9 = function() {
          W && (H(W), W = null)
        },
        ba = function() {
          S && (W = G(ba), bo())
        },
        bb = function(a, b) {
          return !(!a || a === document || a === u.popup.scrollwrap) && (b(a) ? a : bb(a.parentNode, b))
        },
        bc = {},
        bd = function(a, c) {
          return bc.prevent = !bb(a.target, b.isClickableElement), h("preventDragEvent", a, c, bc), bc.prevent
        },
        be = function(b, a) {
          return a.x = b.pageX, a.y = b.pageY, a.id = b.identifier, a
        },
        bf = function(a, b, c) {
          c.x = .5 * (a.x + b.x), c.y = .5 * (a.y + b.y)
        },
        bg = function() {
          return 1 - Math.abs((al.y - d.currItem.initialPosition.y) / (f.y / 2))
        },
        bh = {},
        bi = {},
        bj = [],
        bk = function(a) {
          for (; bj.length > 0;) bj.pop();
          return E ? (ai = 0, a$.forEach(function(a) {
            0 === ai ? bj[0] = a : 1 === ai && (bj[1] = a), ai++
          })) : a.type.indexOf("touch") > -1 ? a.touches && a.touches.length > 0 && (bj[0] = be(a.touches[0], bh), a.touches.length > 1 && (bj[1] = be(a.touches[1], bi))) : (bh.x = a.pageX, bh.y = a.pageY, bh.id = "", bj[0] = bh), bj
        },
        bl = function(a, f) {
          var g, i, j, c, e = al[a] + f[a],
            l = f[a] > 0,
            h = a5.x + f.x,
            k = a5.x - a_.x;
          if (g = e > ab.min[a] || e < ab.max[a] ? b.panEndFriction : 1, e = al[a] + f[a] * g, (b.allowPanToNext || v === d.currItem.initialZoomLevel) && (ac ? "h" !== $ || "x" !== a || U || (l ? (e > ab.min[a] && (g = b.panEndFriction, ab.min[a], i = ab.min[a] - ak[a]), (i <= 0 || k < 0) && bA() > 1 ? (c = h, k < 0 && h > a_.x && (c = a_.x)) : ab.min.x !== ab.max.x && (j = e)) : (e < ab.max[a] && (g = b.panEndFriction, ab.max[a], i = ak[a] - ab.max[a]), (i <= 0 || k > 0) && bA() > 1 ? (c = h, k > 0 && h < a_.x && (c = a_.x)) : ab.min.x !== ab.max.x && (j = e))) : c = h, "x" === a)) return void 0 !== c && (aB(c, !0), X = c !== a_.x), ab.min.x !== ab.max.x && (void 0 !== j ? al.x = j : X || (al.x += f.x * g)), void 0 !== c;
          ad || X || !(v > d.currItem.fitRatio) || (al[a] += f[a] * g)
        },
        bm = function(a) {
          if ("pointerdown" !== a.type || !(a.which > 1 || a.ctrlKey)) {
            if (by) a.preventDefault();
            else {
              if (bd(a, !0) && a.preventDefault(), h("pointerDown"), E) {
                var c = aw.arraySearch(a$, a.pointerId, "id");
                c < 0 && (c = a$.length), a$[c] = {
                  x: a.pageX,
                  y: a.pageY,
                  id: a.pointerId
                }
              }
              var b = bk(a),
                e = b.length;
              Y = null, aT(), S && 1 !== e || (S = ae = !0, aw.bind(window, r, d), Q = ah = af = R = X = V = T = U = !1, $ = null, h("firstTouchStart", b), aD(ak, al), aj.x = aj.y = 0, aD(aY, b[0]), aD(aZ, aY), a_.x = ao.x * am, a0 = [{
                x: aY.x,
                y: aY.y
              }], O = N = au(), aI(v, !0), a9(), ba()), Z || !(e > 1) || ad || X || (w = v, U = !1, Z = T = !0, aj.y = aj.x = 0, aD(ak, al), aD(aV, b[0]), aD(aW, b[1]), bf(aV, aW, a7), a6.x = Math.abs(a7.x) - al.x, a6.y = Math.abs(a7.y) - al.y, aa = a8(aV, aW))
            }
          }
        },
        bn = function(a) {
          if (a.preventDefault(), E) {
            var c = aw.arraySearch(a$, a.pointerId, "id");
            if (c > -1) {
              var d = a$[c];
              d.x = a.pageX, d.y = a.pageY
            }
          }
          if (S) {
            var b = bk(a);
            if ($ || V || Z) Y = b;
            else if (a5.x !== ao.x * am) $ = "h";
            else {
              var e = Math.abs(b[0].x - aY.x) - Math.abs(b[0].y - aY.y);
              Math.abs(e) >= 10 && ($ = e > 0 ? "h" : "v", Y = b)
            }
          }
        },
        bo = function() {
          if (Y) {
            var g = Y.length;
            if (0 !== g) {
              if (aD(aV, Y[0]), aX.x = aV.x - aY.x, aX.y = aV.y - aY.y, Z && g > 1) {
                if (aY.x = aV.x, aY.y = aV.y, !aX.x && !aX.y && (l = Y[1], m = aW, l.x === m.x && l.y === m.y)) return;
                aD(aW, Y[1]), U || (U = !0);
                var l, m, k = a8(aV, aW),
                  a = bt(k);
                a > d.currItem.initialZoomLevel + d.currItem.initialZoomLevel / 15 && (ah = !0);
                var e = 1,
                  c = aJ(),
                  f = aK();
                if (a < c) {
                  if (b.pinchToClose && !ah && w <= d.currItem.initialZoomLevel) {
                    var i = 1 - (c - a) / (c / 1.2);
                    av(i), h("onPinchClose", i), af = !0
                  } else(e = (c - a) / c) > 1 && (e = 1), a = c - e * (c / 3)
                } else a > f && ((e = (a - f) / (6 * c)) > 1 && (e = 1), a = f + e * c);
                e < 0 && (e = 0), bf(aV, aW, a3), aj.x += a3.x - a7.x, aj.y += a3.y - a7.y, aD(a7, a3), al.x = aC("x", a), al.y = aC("y", a), Q = a > v, v = a, ay()
              } else {
                if (!$ || (ae && (ae = !1, Math.abs(aX.x) >= 10 && (aX.x -= Y[0].x - aZ.x), Math.abs(aX.y) >= 10 && (aX.y -= Y[0].y - aZ.y)), aY.x = aV.x, aY.y = aV.y, 0 === aX.x && 0 === aX.y)) return;
                if ("v" === $ && b.closeOnVerticalDrag && v === d.currItem.initialZoomLevel) {
                  aj.y += aX.y, al.y += aX.y;
                  var j = bg();
                  return R = !0, h("onVerticalDrag", j), av(j), void ay()
                }! function(b, c, d) {
                  if (b - O > 50) {
                    var a = a0.length > 2 ? a0.shift() : {};
                    a.x = c, a.y = d, a0.push(a), O = b
                  }
                }(au(), aV.x, aV.y), V = !0, ab = d.currItem.bounds, bl("x", aX) || (bl("y", aX), aE(al), ay())
              }
            }
          }
        },
        bp = function(a) {
          if (h("pointerUp"), bd(a, !1) && a.preventDefault(), E) {
            var i = aw.arraySearch(a$, a.pointerId, "id");
            i > -1 && ((f = a$.splice(i, 1)[0], navigator.msPointerEnabled) ? (f.type = ({
              4: "mouse",
              2: "touch",
              3: "pen"
            })[a.pointerType], f.type || (f.type = a.pointerType || "mouse")) : f.type = a.pointerType || "mouse")
          }
          var f, e, j = bk(a),
            c = j.length;
          if ("mouseup" === a.type && (c = 0), 2 === c) return Y = null, !0;
          1 === c && aD(aZ, j[0]), 0 !== c || $ || ad || (f || ("mouseup" === a.type ? f = {
            x: a.pageX,
            y: a.pageY,
            type: "mouse"
          } : a.changedTouches && a.changedTouches[0] && (f = {
            x: a.changedTouches[0].pageX,
            y: a.changedTouches[0].pageY,
            type: "touch"
          })), h("touchRelease", a, f));
          var g = -1;
          if (0 === c && (S = !1, aw.unbind(window, r, d), a9(), Z ? g = 0 : -1 !== a4 && (g = au() - a4)), a4 = 1 === c ? au() : -1, e = -1 !== g && g < 150 ? "zoom" : "swipe", Z && c < 2 && (Z = !1, 1 === c && (e = "zoomPointerUp"), h("zoomGestureEnded")), Y = null, V || U || ad || R) {
            if (aT(), P || (P = bq()), P.calculateSwipeSpeed("x"), R) {
              if (bg() < b.verticalDragRange) d.close();
              else {
                var k = al.y,
                  l = ag;
                aU("verticalDrag", 0, 1, 300, aw.easing.cubic.out, function(a) {
                  al.y = (d.currItem.initialPosition.y - k) * a + k, av((1 - l) * a + l), ay()
                }), h("onVerticalDrag", 1)
              }
            } else {
              if ((X || ad) && 0 === c) {
                if (bs(e, aY.x - aZ.x, P)) return;
                e = "zoomPointerUp"
              }
              ad || ("swipe" === e ? !X && v > d.currItem.fitRatio && br(P) : bu())
            }
          }
        },
        bq = function() {
          var b, c, a = {
            lastFlickOffset: {},
            lastFlickDist: {},
            lastFlickSpeed: {},
            slowDownRatio: {},
            slowDownRatioReverse: {},
            speedDecelerationRatio: {},
            speedDecelerationRatioAbs: {},
            distanceOffset: {},
            backAnimDestination: {},
            backAnimStarted: {},
            calculateSwipeSpeed: function(d) {
              a0.length > 1 ? (b = au() - O + 50, c = a0[a0.length - 2][d]) : (b = au() - N, c = aZ[d]), a.lastFlickOffset[d] = aY[d] - c, a.lastFlickDist[d] = Math.abs(a.lastFlickOffset[d]), a.lastFlickDist[d] > 20 ? a.lastFlickSpeed[d] = a.lastFlickOffset[d] / b : a.lastFlickSpeed[d] = 0, .1 > Math.abs(a.lastFlickSpeed[d]) && (a.lastFlickSpeed[d] = 0), a.slowDownRatio[d] = .95, a.slowDownRatioReverse[d] = 1 - a.slowDownRatio[d], a.speedDecelerationRatio[d] = 1
            },
            calculateOverBoundsAnimOffset: function(b, c) {
              a.backAnimStarted[b] || (al[b] > ab.min[b] ? a.backAnimDestination[b] = ab.min[b] : al[b] < ab.max[b] && (a.backAnimDestination[b] = ab.max[b]), void 0 !== a.backAnimDestination[b] && (a.slowDownRatio[b] = .7, a.slowDownRatioReverse[b] = 1 - a.slowDownRatio[b], a.speedDecelerationRatioAbs[b] < .05 && (a.lastFlickSpeed[b] = 0, a.backAnimStarted[b] = !0, aU("bounceZoomPan" + b, al[b], a.backAnimDestination[b], c || 300, aw.easing.sine.out, function(a) {
                al[b] = a, ay()
              }))))
            },
            calculateAnimOffset: function(b) {
              a.backAnimStarted[b] || (a.speedDecelerationRatio[b] = a.speedDecelerationRatio[b] * (a.slowDownRatio[b] + a.slowDownRatioReverse[b] - a.slowDownRatioReverse[b] * a.timeDiff / 10), a.speedDecelerationRatioAbs[b] = Math.abs(a.lastFlickSpeed[b] * a.speedDecelerationRatio[b]), a.distanceOffset[b] = a.lastFlickSpeed[b] * a.speedDecelerationRatio[b] * a.timeDiff, al[b] += a.distanceOffset[b])
            },
            panAnimLoop: function() {
              if (aP.zoomPan && (aP.zoomPan.raf = G(a.panAnimLoop), a.now = au(), a.timeDiff = a.now - a.lastNow, a.lastNow = a.now, a.calculateAnimOffset("x"), a.calculateAnimOffset("y"), ay(), a.calculateOverBoundsAnimOffset("x"), a.calculateOverBoundsAnimOffset("y"), a.speedDecelerationRatioAbs.x < .05 && a.speedDecelerationRatioAbs.y < .05)) return al.x = Math.round(al.x), al.y = Math.round(al.y), ay(), void aR("zoomPan")
            }
          };
          return a
        },
        br = function(a) {
          if (a.calculateSwipeSpeed("y"), ab = d.currItem.bounds, a.backAnimDestination = {}, a.backAnimStarted = {}, .05 >= Math.abs(a.lastFlickSpeed.x) && .05 >= Math.abs(a.lastFlickSpeed.y)) return a.speedDecelerationRatioAbs.x = a.speedDecelerationRatioAbs.y = 0, a.calculateOverBoundsAnimOffset("x"), a.calculateOverBoundsAnimOffset("y"), !0;
          aS("zoomPan"), a.lastNow = au(), a.panAnimLoop()
        },
        bs = function(j, f, a) {
          if (u.popup.caption_transition_delay = 0, ad || (a2 = o), "swipe" === j) {
            var g = a.lastFlickDist.x < 10;
            f > 30 && (g || a.lastFlickOffset.x > 20) ? l = -1 : f < -30 && (g || a.lastFlickOffset.x < -20) && (l = 1)
          }
          l && ((o += l) < 0 ? (o = b.loop ? bA() - 1 : 0, m = !0) : o >= bA() && (o = b.loop ? 0 : bA() - 1, m = !0), m && !b.loop || (ap += l, am -= l, c = !0));
          var c, l, m, i, e = ao.x * am,
            k = Math.abs(e - a5.x);
          return i = (c || e > a5.x == a.lastFlickSpeed.x > 0) && Math.abs(a.lastFlickSpeed.x) > 0 ? Math.max(Math.min(k / Math.abs(a.lastFlickSpeed.x), 400), 250) : 333, a2 === o && (c = !1), ad = !0, c && u.popup.toggle_timer(!1), aU("mainScroll", a5.x, e, i, aw.easing.cubic.out, aB, function() {
            aT(), ad = !1, a2 = -1, (c || a2 !== o) && d.updateCurrItem(), h("mainScrollAnimComplete")
          }), c && d.updateCurrItem(!0), c
        },
        bt = function(a) {
          return 1 / aa * a * w
        },
        bu = function() {
          var a = v,
            b = aJ(),
            c = aK();
          v < b ? a = b : v > c && (a = c);
          var e, f = ag;
          return af && !Q && !ah && v < b ? (d.close(), !0) : (af && (e = function(a) {
            av((1 - f) * a + f)
          }), d.zoomTo(a, 0, 200, aw.easing.cubic.out, e), !0)
        };
      a("Gestures", {
        publicMethods: {
          initGestures: function() {
            var a = function(a, c, d, e, b) {
              A = a + c, B = a + d, C = a + e, D = b ? a + b : ""
            };
            (E = L.pointerEvent) && L.touch && (L.touch = !1), E ? a("pointer", "down", "move", "up", "cancel") : L.touch ? (a("touch", "start", "move", "end", "cancel"), F = !0) : a("mouse", "down", "move", "up"), r = B + " " + C + " " + D, s = A, E && !F && (F = e.is_touch), d.likelyTouchDevice = F, t[A] = bm, t[B] = bn, t[C] = bp, D && (t[D] = t[C]), L.dual_input && (s += " mousedown", r += " mousemove mouseup", t.mousedown = t[A], t.mousemove = t[B], t.mouseup = t[C]), F || (b.allowPanToNext = !1)
          }
        }
      });
      var bv, bw, bx, by, bz, bA, bB = function(a, l, c, m) {
          bv && clearTimeout(bv), by = !0, bx = !0, a.initialLayout ? (e = a.initialLayout, a.initialLayout = null) : e = b.getThumbBoundsFn && b.getThumbBoundsFn(o, c);
          var e, f, g, j = c ? b.hideAnimationDuration : b.showAnimationDuration,
            k = function() {
              aR("initialZoom"), c ? (d.template.removeAttribute("style"), u.popup.bg.style.removeProperty("opacity")) : (av(1), l && (l.style.display = "block"), i.classList.add("pswp--animated-in")), h("initialZoom" + (c ? "OutEnd" : "InEnd")), m && m(), by = !1
            };
          if (!j || !e || void 0 === e.x) return h("initialZoom" + (c ? "Out" : "In")), c ? i.style.opacity = 0 : (v = a.initialZoomLevel, aD(al, a.initialPosition), ay(), i.style.opacity = 1, av(1)), void(j ? setTimeout(function() {
            k()
          }, j) : k());
          f = n, g = !d.currItem.src || d.currItem.loadError || b.showHideOpacity, a.miniImg && (a.miniImg.style.webkitBackfaceVisibility = "hidden"), c || (v = e.w / a.w, al.x = e.x, al.y = e.y - J, u.popup[g ? "pswp" : "bg"].style.opacity = .001, ay()), aS("initialZoom"), c && !f && i.classList.remove("pswp--animated-in"), g && (c ? aw.toggle_class(i, "pswp--animate_opacity", !f) : setTimeout(function() {
            i.classList.add("pswp--animate_opacity")
          }, 30)), bv = setTimeout(function() {
            if (h("initialZoom" + (c ? "Out" : "In")), c) {
              var d = e.w / a.w,
                l = {
                  x: al.x,
                  y: al.y
                },
                m = v,
                n = ag,
                b = function(a) {
                  1 === a ? (v = d, al.x = e.x, al.y = e.y - K) : (v = (d - m) * a + m, al.x = (e.x - l.x) * a + l.x, al.y = (e.y - K - l.y) * a + l.y), ay(), g ? i.style.opacity = 1 - a : av(n - a * n)
                };
              f ? aU("initialZoom", 0, 1, j, aw.easing.cubic.out, b, k) : (b(1), bv = setTimeout(k, j + 20))
            } else v = a.initialZoomLevel, aD(al, a.initialPosition), ay(), av(1), g ? i.style.opacity = 1 : av(1), bv = setTimeout(k, j + 20)
          }, c ? 10 : 20)
        },
        bC = {},
        bD = [],
        bE = {
          index: 0,
          errorMsg: '<div class="pswp__error-msg"><a href="%url%" target="_blank">The image</a> could not be loaded.</div>',
          preload: [1, 1],
          getNumItemsFn: function() {
            return bw.length
          }
        },
        bF = function(a, f, c) {
          if (a.src && !a.loadError) {
            var g, d, e, b, h = !c;
            if (bC.x = f.x, bC.y = f.y, h) {
              var i = bC.x / a.w,
                j = bC.y / a.h;
              a.fitRatio = i < j ? i : j, (c = a.fitRatio) > 1 && (c = 1), a.initialZoomLevel = c, a.bounds || (a.bounds = {
                center: {
                  x: 0,
                  y: 0
                },
                max: {
                  x: 0,
                  y: 0
                },
                min: {
                  x: 0,
                  y: 0
                }
              })
            }
            if (!c) return;
            return g = a, d = a.w * c, e = a.h * c, b = g.bounds, b.center.x = Math.round((bC.x - d) / 2), b.center.y = Math.round((bC.y - e) / 2), b.max.x = d > bC.x ? Math.round(bC.x - d) : b.center.x, b.max.y = e > bC.y ? Math.round(bC.y - e) : b.center.y, b.min.x = d > bC.x ? 0 : b.center.x, b.min.y = e > bC.y ? 0 : b.center.y, h && c === a.initialZoomLevel && (a.initialPosition = a.bounds.center), a.bounds
          }
          return a.w = a.h = 0, a.initialZoomLevel = a.fitRatio = 1, a.bounds = {
            center: {
              x: 0,
              y: 0
            },
            max: {
              x: 0,
              y: 0
            },
            min: {
              x: 0,
              y: 0
            }
          }, a.initialPosition = a.bounds.center, a.bounds
        },
        bG = function(f, a, c, b, g, e) {
          a.loadError || b && (a.imageAppended = !0, bJ(a, b, a === d.currItem && ar), c.appendChild(b), e && setTimeout(function() {
            a && a.loaded && a.placeholder && (a.placeholder.style.display = "none", a.placeholder = null)
          }, 500))
        },
        bH = function(a) {
          a.loading = !0, a.loaded = !1;
          var b = a.img = aw.createEl("pswp__img", "img"),
            c = function() {
              a.loading = !1, a.loaded = !0, a.loadComplete ? a.loadComplete(a) : a.img = null, b.onload = b.onerror = null, b = null
            };
          return b.onload = c, b.onerror = function() {
            a.loadError = !0, c()
          }, b.src = a.src, b
        },
        bI = function(a, c) {
          if (a.src && a.loadError && a.container) return c && aw.resetEl(a.container), a.container.innerHTML = b.errorMsg.replace("%url%", a.src), !0
        },
        bJ = function(a, b, c) {
          if (a.src) {
            b || (b = a.container.lastElementChild);
            var d = c ? a.w : Math.round(a.w * a.fitRatio),
              e = c ? a.h : Math.round(a.h * a.fitRatio);
            a.placeholder && !a.loaded && (a.placeholder.style.width = d + "px", a.placeholder.style.height = e + "px"), b.style.width = d + "px", b.style.height = e + "px"
          }
        },
        bK = function() {
          if (bD.length) {
            for (var a, b = 0; b < bD.length; b++)(a = bD[b]).holder.index === a.index && bG(a.index, a.item, a.baseDiv, a.img, 0, a.clearPlaceholder);
            bD = []
          }
        };
      a("Controller", {
        publicMethods: {
          lazyLoadItem: function(b) {
            b = as(b);
            var a = bz(b);
            a && (!a.loaded && !a.loading || x) && (h("gettingData", b, a), a.src && bH(a))
          },
          initController: function() {
            aw.copy_unique(b, bE), d.items = bw = k, bz = d.getItemAt, bA = b.getNumItemsFn, b.loop, 3 > bA() && (b.loop = !1), g("beforeChange", function(c) {
              var a, e = b.preload,
                f = null === c || c >= 0,
                g = Math.min(e[0], bA()),
                h = Math.min(e[1], bA());
              for (a = 1; a <= (f ? h : g); a++) d.lazyLoadItem(o + a);
              for (a = 1; a <= (f ? g : h); a++) d.lazyLoadItem(o - a)
            }), g("initialLayout", function() {
              d.currItem.initialLayout = b.getThumbBoundsFn && b.getThumbBoundsFn(o)
            }), g("mainScrollAnimComplete", bK), g("initialZoomInEnd", bK), g("destroy", function() {
              for (var a, b = 0; b < bw.length; b++)(a = bw[b]).container && (a.container = null), a.placeholder && (a.placeholder = null), a.img && (a.img = null), a.preloader && (a.preloader = null), a.loadError && (a.loaded = a.loadError = !1);
              bD = null
            })
          },
          getItemAt: function(a) {
            return a >= 0 && void 0 !== bw[a] && bw[a]
          },
          setContent: function(e, g) {
            b.loop && (g = as(g));
            var m = d.getItemAt(e.index);
            m && (m.container = null);
            var i, a = d.getItemAt(g);
            if (a) {
              h("gettingData", g, a), e.index = g, e.item = a;
              var c = a.container = aw.createEl("pswp__zoom-wrap");
              if (!a.src && a.html && (a.html.tagName ? c.appendChild(a.html) : c.innerHTML = a.html), bI(a), bF(a, f), !a.src || a.loadError || a.loaded) a.src && !a.loadError && ((i = aw.createEl("pswp__img", "img")).style.opacity = 1, i.src = a.src, bJ(a, i), bG(0, a, c, i));
              else {
                a.loadComplete = function(a) {
                  if (l) {
                    if (e && e.index === g) {
                      if (bI(a, !0)) return a.loadComplete = a.img = null, bF(a, f), az(a), void(e.index === o && d.updateCurrZoomItem());
                      a.imageAppended ? !by && a.placeholder && (a.placeholder.style.display = "none", a.placeholder = null) : ad || by ? bD.push({
                        item: a,
                        baseDiv: c,
                        img: a.img,
                        index: g,
                        holder: e,
                        clearPlaceholder: !0
                      }) : bG(0, a, c, a.img, 0, !0)
                    }
                    a.loadComplete = null, a.img = null, h("imageLoadComplete", g, a)
                  }
                };
                var k = a.msrc && (a.msrc !== a.src || !bx),
                  j = aw.createEl("pswp__img pswp__img--placeholder" + (k ? "" : " pswp__img--placeholder--blank"), k ? "img" : "");
                k && (j.src = a.msrc), bJ(a, j), c.appendChild(j), a.placeholder = j, a.loading || bH(a), bx ? bG(0, a, c, a.img, 0, !0) : bD.push({
                  item: a,
                  baseDiv: c,
                  img: a.img,
                  index: g,
                  holder: e
                })
              }
              bx || g !== o ? az(a) : (ac = c.style, bB(a, i || a.img)), aw.resetEl(e.el), e.el.appendChild(c)
            } else aw.resetEl(e.el)
          },
          cleanSlide: function(a) {
            a.img && (a.img.onload = a.img.onerror = null), a.loaded = a.loading = a.img = a.imageAppended = !1
          }
        }
      });
      var bL, bM, bN = {},
        bO = function(a, d, b) {
          var c = document.createEvent("CustomEvent"),
            e = {
              origEvent: a,
              pointerType: b || "touch",
              releasePoint: d,
              target: a.target,
              rightClick: "mouse" === b && 3 === a.which
            };
          c.initCustomEvent("pswpTap", !0, !0, e), a.target.dispatchEvent(c)
        };
      a("Tap", {
        publicMethods: {
          initTap: function() {
            g("firstTouchStart", d.onTapStart), g("touchRelease", d.onTapRelease), g("destroy", function() {
              bN = {}, bL = null
            })
          },
          onTapStart: function(a) {
            a.length > 1 && (clearTimeout(bL), bL = null)
          },
          onTapRelease: function(a, b) {
            var c, d;
            if (b && !V && !T && !aQ && (!E || u.popup.container.contains(a.target))) {
              var e = b;
              if (bL && (clearTimeout(bL), bL = null, c = e, d = bN, 25 > Math.abs(c.x - d.x) && 25 > Math.abs(c.y - d.y))) return void h("doubleTap", e);
              if ("mouse" === b.type) return void bO(a, b, "mouse");
              if ("A" === a.target.tagName) return;
              if ("BUTTON" === a.target.tagName || a.target.classList.contains("pswp__single-tap")) return void bO(a, b);
              aD(bN, e), bL = setTimeout(function() {
                bO(a, b), bL = null
              }, 300)
            }
          }
        }
      }), a("DesktopZoom", {
        publicMethods: {
          initDesktopZoom: function() {
            e.is_dual_input ? g("mouseUsed", function() {
              d.setupDesktopZoom()
            }) : e.is_pointer && d.setupDesktopZoom(!0)
          },
          setupDesktopZoom: function(b) {
            bM = {};
            var e = "wheel mousewheel DOMMouseScroll";
            g("bindEvents", function() {
              aw.bind(i, e, d.handleMouseWheel)
            }), g("unbindEvents", function() {
              bM && aw.unbind(i, e, d.handleMouseWheel)
            }), d.mouseZoomedIn = !1;
            var f, a = function() {
                d.mouseZoomedIn && (i.classList.remove("pswp--zoomed-in"), d.mouseZoomedIn = !1), aw.toggle_class(i, "pswp--zoom-allowed", v < 1), c()
              },
              c = function() {
                f && (i.classList.remove("pswp--dragging"), f = !1)
              };
            g("resize", a), g("afterChange", a), g("pointerDown", function() {
              d.mouseZoomedIn && (f = !0, i.classList.add("pswp--dragging"))
            }), g("pointerUp", c), b || a()
          },
          handleMouseWheel: function(a) {
            if (v <= d.currItem.fitRatio) return !b.closeOnScroll || aQ || S ? a.preventDefault() : Math.abs(a.deltaY) > 2 && (n = !0, d.close()), !0;
            if (a.stopPropagation(), bM.x = 0, "deltaX" in a) 1 === a.deltaMode ? (bM.x = 18 * a.deltaX, bM.y = 18 * a.deltaY) : (bM.x = a.deltaX, bM.y = a.deltaY);
            else if ("wheelDelta" in a) a.wheelDeltaX && (bM.x = -0.16 * a.wheelDeltaX), a.wheelDeltaY ? bM.y = -0.16 * a.wheelDeltaY : bM.y = -0.16 * a.wheelDelta;
            else {
              if (!("detail" in a)) return;
              bM.y = a.detail
            }
            aI(v, !0);
            var c = al.x - bM.x,
              e = al.y - bM.y;
            a.preventDefault(), d.panTo(c, e)
          },
          toggleDesktopZoom: function(a) {
            a = a || {
              x: f.x / 2 + an.x,
              y: f.y / 2 + an.y
            };
            var e = b.getDoubleTapZoom(!0, d.currItem),
              c = v === e;
            d.mouseZoomedIn = !c, d.zoomTo(c ? d.currItem.initialZoomLevel : e, a, 333), aw.toggle_class(i, "pswp--zoomed-in", !c)
          }
        }
      });
      var bP, bQ, bR, bS, bT, bU, bV, bW, bX, bY, bZ = {
          history: !0
        },
        b$ = function() {
          return bY.hash.substring(1)
        },
        b_ = function() {
          bP && clearTimeout(bP), bR && clearTimeout(bR)
        },
        b0 = function() {
          if (bR && clearTimeout(bR), aQ || S) bR = setTimeout(b0, 500);
          else {
            bS ? clearTimeout(bQ) : bS = !0;
            var b = o + 1,
              c = bz(o);
            c.hasOwnProperty("pid") && (b = c.pid);
            var a = (bU ? bU + "&" : "") + "pid=" + b;
            bV || -1 === bY.hash.indexOf(a) && (bX = !0);
            var d = bY.href.split("#")[0] + "#" + a;
            "#" + a !== window.location.hash && history[bV ? "replaceState" : "pushState"]("", document.title, d), bV = !0, bQ = setTimeout(function() {
              bS = !1
            }, 60)
          }
        };
      a("History", {
        publicMethods: {
          initHistory: function() {
            if (aw.copy_unique(b, bZ), b.history) {
              bY = window.location, bX = !1, bW = !1, bV = !1, bU = b$(), g("afterChange", d.updateURL), g("unbindEvents", function() {
                aw.unbind(window, "hashchange", d.onHashChange)
              }), history.scrollRestoration && (history.scrollRestoration = "manual");
              var c = function() {
                bT = !0, bW || (bX ? history.back() : bU ? bY.hash = bU : history.pushState("", document.title, bY.pathname + bY.search)), b_(), history.scrollRestoration && (history.scrollRestoration = "auto")
              };
              g("unbindEvents", function() {
                n && c()
              }), g("destroy", function() {
                bT || c()
              });
              var a = bU.indexOf("pid=");
              a > -1 && "&" === (bU = bU.substring(0, a)).slice(-1) && (bU = bU.slice(0, -1)), setTimeout(function() {
                l && aw.bind(window, "hashchange", d.onHashChange)
              }, 40)
            }
          },
          onHashChange: function() {
            if (b$() === bU) return bW = !0, void d.close()
          },
          updateURL: function() {
            b_(), bV ? bP = setTimeout(b0, 800) : b0()
          }
        }
      }), Object.assign(d, {
        shout: h,
        listen: g,
        viewportSize: f,
        options: b,
        isMainScrollAnimating: function() {
          return ad
        },
        getZoomLevel: function() {
          return v
        },
        getCurrentIndex: function() {
          return o
        },
        isDragging: function() {
          return S
        },
        isZooming: function() {
          return Z
        },
        setScrollOffset: function(a, b) {
          an.x = a, K = an.y = b
        },
        applyZoomPan: function(a, b, c, d) {
          al.x = b, al.y = c, v = a, ay(d)
        },
        init: function() {
          if (!l && !m) {
            for (d.framework = aw, d.template = i, I = i.className, l = !0, G = (L = aw.features).raf, H = L.caf, p = u.popup.container.style, d.itemHolders = y = [{
                el: u.popup.items[0],
                wrap: 0,
                index: -1
              }, {
                el: u.popup.items[1],
                wrap: 0,
                index: -1
              }, {
                el: u.popup.items[2],
                wrap: 0,
                index: -1
              }], y[0].el.style.display = y[2].el.style.display = "none", _ = "translate" + (F ? "(" : "3d("), t = {
                resize: d.updateSize,
                orientationchange: function() {
                  clearTimeout(M), M = setTimeout(function() {
                    f.x !== u.popup.scrollwrap.clientWidth && d.updateSize()
                  }, 500)
                },
                scroll: aO,
                keydown: aM,
                click: aN
              }, a = 0; a < aq.length; a++) d["init" + aq[a]]();
            j && (d.ui = new j(d, aw)).init(), h("firstUpdate"), o = o || b.index || 0, (isNaN(o) || o < 0 || o >= bA()) && (o = 0), d.currItem = bz(o), i.setAttribute("aria-hidden", "false"), void 0 === K && (h("initialLayout"), K = J = aw.getScrollY());
            var a, c = "pswp--open" + (b.showHideOpacity ? " pswp--animate_opacity" : "") + (e.is_pointer && ("zoom" == b.click || 2 > b.getNumItemsFn()) ? " pswp--zoom-cursor" : "");
            for (DOMTokenList.prototype.add.apply(i.classList, c.split(" ")), d.updateSize(), q = -1, ap = null, a = 0; a < 3; a++) aA((a + q) * ao.x, y[a].el.style);
            aw.bind(u.popup.scrollwrap, s, d), g("initialZoomInEnd", function() {
              d.setContent(y[0], o - 1), d.setContent(y[2], o + 1), y[0].el.style.display = y[2].el.style.display = "block", b.focus && i.focus(), aw.bind(document, "keydown", d), aw.bind(u.popup.scrollwrap, "click", d), L.is_mouse ? aF() : e.is_pointer && aw.bind(document, "mousemove", aH), aw.bind(window, "resize scroll orientationchange", d), h("bindEvents")
            }), d.setContent(y[1], o), d.updateCurrItem(), i.classList.add("pswp--visible")
          }
        },
        close: function() {
          l && (l = !1, m = !0, h("close"), setTimeout(function() {
            aw.unbind(window, "resize scroll orientationchange", d)
          }, 400), aw.unbind(window, "scroll", t.scroll), aw.unbind(document, "keydown", d), e.is_pointer && aw.unbind(document, "mousemove", aH), aw.unbind(u.popup.scrollwrap, "click", d), S && aw.unbind(window, r, d), clearTimeout(M), h("unbindEvents"), bB(d.currItem, null, !0, d.destroy))
        },
        destroy: function() {
          h("destroy"), bv && clearTimeout(bv), i.setAttribute("aria-hidden", "true"), i.className = I, aw.unbind(u.popup.scrollwrap, s, d), aw.unbind(window, "scroll", d), a9(), aT(), at = {}
        },
        panTo: function(a, b, c) {
          c || (a > ab.min.x ? a = ab.min.x : a < ab.max.x && (a = ab.max.x), b > ab.min.y ? b = ab.min.y : b < ab.max.y && (b = ab.max.y)), a == al.x && b == al.y || (al.x = a, al.y = b, ay())
        },
        handleEvent: function(a) {
          t[(a = a || window.event).type] && t[a.type](a)
        },
        goTo: function(a, h, i) {
          var e = i ? b.play_transition : b.transition;
          if ("slide" === e) bs("swipe", 80 * a, {
            lastFlickDist: {
              x: 80,
              y: 0
            },
            lastFlickOffset: {
              x: 80 * a,
              y: 0
            },
            lastFlickSpeed: {
              x: 2 * a,
              y: 0
            }
          });
          else {
            var f = (a = as(a)) - o;
            ap = f, o = a, d.currItem = bz(o), am -= f, aB(ao.x * am), aT(), ad = !1, u.popup.image_anim && !u.popup.image_anim.paused && u.popup.image_anim.pause();
            var c = !!u.popup.transitions.hasOwnProperty(e) && u.popup.transitions[e](h);
            if (u.popup.caption_transition_delay = c && c.duration || 0, d.updateCurrItem(), !c) return;
            var g = !!d.currItem.container && d.currItem.container.lastElementChild;
            g && (u.popup.image_timer ? clearTimeout(u.popup.image_timer) : u.popup.image_anim = anime(Object.assign({
              targets: g
            }, c)), u.popup.image_timer = setTimeout(function() {
              u.popup.image_timer = !1
            }, 300))
          }
        },
        next: function(a) {
          if (b.loop || o !== bA() - 1) {
            var c = a ? b.play_transition : b.transition;
            d.goTo("slide" === c ? -1 : parseInt(o) + 1, 1, a)
          }
        },
        prev: function() {
          (b.loop || 0 !== o) && d.goTo("slide" === b.transition ? 1 : parseInt(o) - 1, -1)
        },
        updateCurrZoomItem: function(b) {
          b && h("beforeChange", 0);
          var a = y[1].el.children;
          ac = a.length && a[0].classList.contains("pswp__zoom-wrap") ? a[0].style : null, ab = d.currItem.bounds, w = v = d.currItem.initialZoomLevel, al.x = ab.center.x, al.y = ab.center.y, b && h("afterChange")
        },
        invalidateCurrItems: function() {
          x = !0;
          for (var a = 0; a < 3; a++) y[a].item && (y[a].item.needsUpdate = !0)
        },
        updateCurrItem: function(g) {
          if (0 !== ap) {
            var a, b = Math.abs(ap);
            if (!(g && b < 2)) {
              d.currItem = bz(o), ar = !1, h("beforeChange", ap), b >= 3 && (q += ap + (ap > 0 ? -3 : 3), b = 3);
              for (var c = 0; c < b; c++) ap > 0 ? (a = y.shift(), y[2] = a, q++, aA((q + 2) * ao.x, a.el.style), d.setContent(a, o - b + c + 1 + 1)) : (a = y.pop(), y.unshift(a), q--, aA(q * ao.x, a.el.style), d.setContent(a, o + b - c - 1 - 1));
              if (ac && 1 === Math.abs(ap)) {
                var e = bz(z);
                e.initialZoomLevel !== v && (bF(e, f), bJ(e), az(e))
              }
              ap = 0, d.updateCurrZoomItem(), z = o, h("afterChange")
            }
          }
        },
        updateSize: function(i) {
          if (f.x = u.popup.scrollwrap.clientWidth, f.y = u.popup.scrollwrap.clientHeight, aO(), ao.x = f.x + Math.round(f.x * b.spacing), ao.y = f.y, aB(ao.x * am), h("beforeResize"), void 0 !== q) {
            for (var g, a, c, e = 0; e < 3; e++) g = y[e], aA((e + q) * ao.x, g.el.style), c = o + e - 1, b.loop && bA() > 2 && (c = as(c)), (a = bz(c)) && (x || a.needsUpdate || !a.bounds) ? (d.cleanSlide(a), d.setContent(g, c), 1 === e && (d.currItem = a, d.updateCurrZoomItem(!0)), a.needsUpdate = !1) : -1 === g.index && c >= 0 && d.setContent(g, c), a && a.container && (bF(a, f), bJ(a), az(a));
            x = !1
          }
          w = v = d.currItem.initialZoomLevel, (ab = d.currItem.bounds) && (al.x = ab.center.x, al.y = ab.center.y, ay(!0)), h("resize")
        },
        zoomTo: function(a, b, d, g, h) {
          b && (w = v, a6.x = Math.abs(b.x) - al.x, a6.y = Math.abs(b.y) - al.y, aD(ak, al));
          var e = aI(a, !1),
            c = {};
          aL("x", e, c, a), aL("y", e, c, a);
          var i = v,
            j = {
              x: al.x,
              y: al.y
            };
          aE(c);
          var f = function(b) {
            1 === b ? (v = a, al.x = c.x, al.y = c.y) : (v = (a - i) * b + i, al.x = (c.x - j.x) * b + j.x, al.y = (c.y - j.y) * b + j.y), h && h(b), ay(1 === b)
          };
          d ? aU("customZoomTo", 0, 1, d, g || aw.easing.sine.inOut, f) : f(1)
        }
      })
    };

  function q() {
    function a(a) {
      setTimeout(function() {
        a && a.remove()
      }, 100)
    } [".modal-body", "#files"].forEach(function(b) {
      yall({
        observeChanges: !0,
        observeRootSelector: b,
        lazyClass: "files-lazy",
        threshold: 300,
        events: {
          load: function(d) {
            var b = d.target;
            if (b.classList.contains("files-folder-preview")) {
              var c = b.naturalWidth;
              return c && 1 === c ? a(b) : b.style.opacity = 1
            }
            b.classList.remove("files-img-placeholder"), b.parentElement.classList.add("files-a-loaded")
          },
          error: {
            listener: function(c) {
              var b = c.target;
              b.classList.contains("files-folder-preview") && a(b)
            }
          }
        }
      })
    }), _query(".preloader-body").remove(), document.body.classList.remove("body-loading"), !_c.prevent_right_click && _c.context_menu && (ag.files_container && ag.files_container.addEventListener("contextmenu", function(b) {
      var a = b.target.closest(".files-a");
      f.create_contextmenu(b, "files", a || ag.files_container, a ? _c.files[a.dataset.name] : _c.current_dir)
    }), ag.sidebar_menu && ag.sidebar_menu.addEventListener("contextmenu", function(b) {
      var a = b.target.closest(".menu-li");
      f.create_contextmenu(b, "sidebar", a || ag.sidebar_menu, _c.dirs[a ? a.dataset.path : ""])
    })), anime({
      targets: document.body,
      opacity: [0, 1],
      duration: 500,
      easing: "easeOutQuad",
      complete: f.init_files
    })
  }! function() {
    if (_c.menu_exists) {
      ag.sidebar = _id("sidebar"), ag.sidebar_inner = _id("sidebar-inner"), ag.sidebar_menu = _id("sidebar-menu"), ag.sidebar_toggle = _id("sidebar-toggle"), ag.sidebar_modal = _id("sidebar-bg"), ag.sidebar_topbar = _id("sidebar-topbar");
      var d, g, h, k, l, m = !1,
        n = !1,
        p = {},
        q = !1,
        r = j.get_json("files:interface:menu-expanded:" + _c.location_hash),
        b = _c.menu_show && matchMedia("(min-width: 992px)").matches;
      b || document.documentElement.classList.add("sidebar-closed"), f.menu_loading = function(a, b) {
        a || (a = n), a && a.classList.toggle("menu-spinner", b)
      }, f.set_menu_active = function(b) {
        var a = n,
          c = !!_c.dirs[b] && _c.dirs[b].menu_li;
        (n = !!c && c.firstChild) != a && (a && f.menu_loading(a, !1), t(a, !1), t(n, !0))
      }, ag.sidebar_toggle.innerHTML = f.get_svg_icon_multi("menu", "menu_back"), s(ag.sidebar_toggle, w, "click"), s(ag.sidebar_modal, w, "click"), f.create_menu = B;
      var a = j.get_json("files:menu:" + _c.menu_cache_hash),
        c = _c.menu_cache_validate || _c.cache && !_c.menu_cache_file;
      !a || c && ! function() {
        for (var c = a.length, b = 0; b < c; b++)
          if (a[b].path.includes("/")) return !1;
        return !0
      }() ? (ag.sidebar_menu.classList.add("sidebar-spinner"), ag.sidebar_menu.dataset.title = aj.get("loading"), Y({
        params: !_c.menu_cache_file && "action=dirs" + (_c.cache ? "&menu_cache_hash=" + _c.menu_cache_hash : "") + (a ? "&localstorage=1" : ""),
        url: _c.menu_cache_file,
        json_response: !0,
        complete: function(b, d, c) {
          if (ag.sidebar_menu.classList.remove("sidebar-spinner"), delete ag.sidebar_menu.dataset.title, !c || !b || b.error || !Object.keys(b).length) return am(), void o("Error or no dirs!");
          b.localstorage ? B(a, "menu from localstorage") : (B(b, "menu from " + (_c.menu_cache_file ? "JSON cache: " + _c.menu_cache_file : "xmlhttp")), e.local_storage && setTimeout(function() {
            f.clean_localstorage(), j.set("files:menu:" + _c.menu_cache_hash, d)
          }, 1e3))
        }
      })) : B(a, "menu from localstorage [" + (_c.menu_cache_validate ? "shallow menu" : "menu cache validation disabled") + "]")
    }

    function t(a, c) {
      if (a && a.isConnected) {
        a.classList.toggle("menu-active", c);
        for (var b = a.parentElement.parentElement.parentElement;
          "LI" === b.nodeName;) b.classList.toggle("menu-active-ancestor", c), b = b.parentElement.parentElement
      }
    }

    function _(b, c) {
      if ("all" === b) c ? i(h, function(a) {
        p[a.dataset.path] = !0
      }) : p = {};
      else {
        var a = b.dataset.path;
        c ? p[a] = !0 : p[a] && delete p[a]
      }
      var g = Object.keys(p).length,
        f = g === h.length;
      q !== f && (q = f, e.is_pointer && (d.title = aj.get(q ? "collapse menu" : "expand menu")), d.classList.toggle("is-expanded", q)), e.local_storage && (m && clearTimeout(m), m = setTimeout(function() {
        j.set("files:interface:menu-expanded:" + _c.location_hash, !!g && JSON.stringify(p), !0)
      }, 1e3))
    }

    function v(c, b, d) {
      var a = c.lastChild;
      a.style.display = "block", anime.remove(a), anime({
        targets: a,
        translateY: b ? [-5, 0] : -5,
        height: [a.clientHeight + "px", b ? a.scrollHeight + "px" : 0],
        opacity: b ? 1 : 0,
        easing: "easeOutQuint",
        duration: 250,
        complete: function() {
          a.style.cssText = "--depth:" + (c.dataset.level || 0), d && d()
        }
      }), c.classList.toggle("menu-li-open", b)
    }

    function w(a) {
      f.set_config("menu_show", !_c.menu_show), document.documentElement.classList.toggle("sidebar-closed"), b = !b
    }

    function x(c, d) {
      for (var a = "", b = 0; b < d; b++) a += c;
      return a
    }

    function A(a, b) {
      var c = "menu-li",
        d = "menu-a",
        g = a.path ? (a.path.match(/\//g) || []).length + 1 : 0,
        e = "folder" + (a.is_readable ? a.is_link ? "_link" : "" : "_forbid");
      return b ? (c += " has-ul", r && r[a.path] && (c += " menu-li-open", p[a.path] = !0)) : a.is_readable || (d += " menu-a-forbidden"), '<li data-level="' + g + '" data-path="' + y(a.path) + '" class="' + c + '"><a href="' + E(a) + '" class="' + d + '">' + (b ? f.get_svg_icon_multi_class("menu-icon menu-icon-toggle", "plus", "minus") : "") + (b ? f.get_svg_icon_multi_class("menu-icon menu-icon-folder menu-icon-folder-toggle", e, "folder_plus", "folder_minus") : f.get_svg_icon_class(e, "menu-icon menu-icon-folder")) + z(a.basename) + "</a>"
    }

    function B(a, y) {
      var z, B, c, C, r, t;
      if (am(), o(y, a), i(a, a => {
          _c.dirs[a.path] || (_c.dirs[a.path] = a)
        }), c = "", C = 0, r = 0, t = !1, i(a, function(e, f) {
          var b = e.path;
          if (b) {
            var d = (b.match(/\//g) || []).length + 1,
              a = d - C;
            C = d, r += a, t && (c += A(t, a > 0)), c += a > 0 ? '<ul style="--depth:' + (r - 1) + '" class="menu-' + (t ? "ul" : "root") + '">' : "</li>" + x("</ul></li>", -a), t = _c.dirs[b]
          }
        }), c += A(t, !1) + x("</li></ul>", r), ag.sidebar_menu.innerHTML = c, z = k = (h = _class("has-ul", g = ag.sidebar_menu.firstChild)).length ? U(Array.from(g.children), "has-ul", !0) : [], l = h.filter(function(a) {
          return !z.includes(a)
        }), i(_class("menu-li", g), function(a) {
          var b = _c.dirs[a.dataset.path];
          b && (b.menu_li = a)
        }), f.set_menu_active(_c.current_path || _c.init_path), e.local_storage && (ag.sidebar_menu.scrollTop = j.get("files:interface:menu_scroll:" + _c.location_hash) || 0, s(ag.sidebar_menu, R(function() {
          j.set("files:interface:menu_scroll:" + _c.location_hash, ag.sidebar_menu.scrollTop, !0)
        }, 1e3), "scroll")), h.length && (B = !1, q = Object.keys(p).length === h.length, ag.sidebar_topbar.innerHTML = '<button id="menu-toggle" type="button" class="btn-icon' + (q ? " is-expanded" : "") + '">' + f.get_svg_icon_multi("plus", "minus") + "</button>", s(d = ag.sidebar_topbar.lastElementChild, function(e) {
          if (q) {
            var b = [],
              a = [],
              c = !1,
              d = window.innerHeight;
            i(k, function(e) {
              if (e.classList.contains("menu-li-open")) {
                if (c) b.push(e);
                else {
                  var f = e.getBoundingClientRect();
                  f.top > d || f.bottom - f.top > 2 * d ? (b.push(e), c = !0) : a.push(e)
                }
              }
            }), b.length && i(b, function(a) {
              a.classList.remove("menu-li-open")
            }), a.length && i(a, function(a) {
              v(a, !1)
            }), B && clearTimeout(B), B = setTimeout(function() {
              T(l, "menu-li-open", !1)
            }, a.length ? 260 : 10)
          } else h.length > 100 ? T(h, "menu-li-open", !0) : (b = [], a = [], c = !1, d = window.innerHeight, i(h, function(e) {
            e.classList.contains("menu-li-open") || (c || !e.offsetParent ? b.push(e) : e.getBoundingClientRect().top > d || e.lastChild.childNodes.length > 50 ? (c = !0, b.push(e)) : a.push(e))
          }), b.length && i(b, function(a) {
            a.classList.add("menu-li-open")
          }), a.length && i(a, function(a) {
            v(a, !0)
          }));
          _("all", !q)
        }, "click")), _c.transitions && b) {
        var m = {
          targets: function() {
            for (var a = [], c = g.children, e = c.length, f = ag.sidebar_inner.clientHeight, b = 0; b < e; b++) {
              var d = c[b];
              if (d.getBoundingClientRect().top < f) a.push(d);
              else if (a.length) break
            }
            return a
          }(),
          translateY: [-5, 0],
          opacity: [0, 1],
          easing: "easeOutCubic",
          duration: 100
        };
        m.delay = anime.stagger(aa(20, 50, Math.round(200 / m.targets.length))), anime(m)
      }
      s(g, function(a) {
        if (u.contextmenu.is_open) return a.preventDefault();
        if (a.target !== g) {
          var d, h, e, i, c = "A" === a.target.nodeName,
            b = c ? a.target.parentElement : a.target.closest(".menu-li"),
            j = c ? a.target : b.firstElementChild;
          if (!Q(a, j)) {
            if (c && j !== n) f.get_files(b.dataset.path, "push"), matchMedia("(min-width: 992px)").matches ? _c.menu_show || (d = ag.sidebar, h = "sidebar-clicked", e = null, i = 1e3, d.classList.add(h), e && (d.disabled = e), setTimeout(function() {
              d.classList.remove([h]), e && (d.disabled = !1)
            }, i || 2e3)) : w();
            else if (!c || b.classList.contains("has-ul")) {
              var k = !b.classList.contains("menu-li-open");
              _(b, k), v(b, k)
            }
          }
        }
      })
    }
  }(),
  function() {
    function d(a, b) {
      Object.assign(u.sort, {
        sort: a,
        order: b,
        multi: "asc" === b ? 1 : -1,
        index: u.sort.keys.indexOf(a),
        prop: u.sort.sorting[a].prop
      })
    }
    u.sort = {
      sorting: {
        name: {
          prop: "basename",
          order: "asc"
        },
        kind: {
          prop: "ext",
          order: "asc"
        },
        size: {
          prop: "filesize",
          order: "desc"
        },
        date: {
          prop: "mtime",
          order: "desc"
        }
      }
    }, u.sort.keys = Object.keys(u.sort.sorting);
    var a = (_c.sort || "name_asc").split("_");
    u.sort.keys.includes(a[0]) || (a[0] = "name"), a[1] && ["asc", "desc"].includes(a[1]) || (a[1] = u.sort.sorting[a[0]].order), a.join("_") !== _c.sort && (_c.sort = a.join("_")), d(a[0], a[1]);
    var b = _id("change-sort");
    b.innerHTML = '<button type="button" class="btn-icon btn-topbar">' + f.get_svg_icon("sort_" + u.sort.sort + "_" + u.sort.order) + '</button><div class="dropdown-menu dropdown-menu-topbar dropdown-menu-center"><h6 class="dropdown-header" data-lang="sort">' + aj.get("sort") + "</h6>" + V(u.sort.keys, function(a) {
      return '<button class="dropdown-item' + (a === u.sort.sort ? " active sort-" + u.sort.order : "") + '" data-action="' + a + '">' + f.get_svg_icon_multi("menu_down", "menu_up") + f.get_svg_icon_multi("sort_" + a + "_asc", "sort_" + a + "_desc") + '<span class="dropdown-text" data-lang="' + a + '">' + aj.get(a) + "</span></button>"
    }) + "</div>";
    var e = b.firstChild,
      c = (b.children[1], b.lastChild),
      h = _class("dropdown-item", c);

    function i(b, a) {
      if (_c.sort_dirs_first && b._values.is_dir !== a._values.is_dir) return (a._values.is_dir ? 1 : -1) * u.sort.multi;
      var c = b._values[u.sort.prop],
        d = a._values[u.sort.prop];
      return "name" === u.sort.sort || c === d ? k(b._values.basename, a._values.basename) : c > d ? 1 : -1
    }
    var g = {
        locale: function(a, b) {
          return j.compare(a, b) || g.basic(a, b)
        },
        basic: function(a, b) {
          var c = a.toLowerCase(),
            d = b.toLowerCase();
          return c === d ? a > b ? 1 : -1 : c > d ? 1 : -1
        }
      },
      j = new Intl.Collator(_c.sort_function && !["basic", "locale"].includes(_c.sort_function) ? _c.sort_function.trim() : void 0, {
        numeric: !0,
        sensitivity: "base"
      }),
      k = g["basic" === _c.sort_function ? "basic" : "locale"];

    function l(b, c, d) {
      var a = d ? "add" : "remove";
      b && (h[u.sort.index].classList[a]("active"), m[u.sort.index].classList[a]("sortbar-active")), (b || c) && (h[u.sort.index].classList[a]("sort-" + u.sort.order), m[u.sort.index].classList[a]("sort-" + u.sort.order))
    }
    f.set_sort = function(a) {
      if (a) {
        var b = a !== u.sort.sort,
          c = b ? u.sort.sorting[a].order : "asc" === u.sort.order ? "desc" : "asc",
          g = c !== u.sort.order;
        l(b, g, !1), d(a, c), e.innerHTML = f.get_svg_icon("sort_" + a + "_" + c), l(b, g, !0), f.set_config("sort", u.sort.sort + "_" + u.sort.order)
      }
      _c.debug && console.time("sort"), u.list.sort(u.sort.prop, {
        order: u.sort.order,
        sortFunction: i
      }), _c.debug && console.timeEnd("sort")
    }, f.dropdown(b, e, function() {
      f.set_sort(u.sort.keys[u.sort.index >= u.sort.keys.length - 1 ? 0 : u.sort.index + 1])
    }), I(c, f.set_sort), ag.sortbar = _id("files-sortbar"), ag.sortbar.className = "sortbar-" + _c.layout, ag.sortbar.innerHTML = '<div class="sortbar-inner">' + V(u.sort.keys, function(a) {
      return '<div class="sortbar-item sortbar-' + a + (a === u.sort.sort ? " sortbar-active sort-" + u.sort.order : "") + '" data-action="' + a + '"><span data-lang="' + a + '" class="sortbar-item-text">' + aj.get(a) + "</span>" + f.get_svg_icon_multi("menu_down", "menu_up") + "</div>"
    }) + "</div>";
    var m = ag.sortbar.firstChild.children;
    s(ag.sortbar, function(a) {
      var b = a.target.closest("[data-action]");
      b && f.set_sort(b.dataset.action, a)
    })
  }(),
  function() {
    if (ag.topbar_top = _id("topbar-top"), u.topbar = {
        info: {}
      }, ag.filter.placeholder = aj.get("filter"), ag.filter.title = e.c_key + "F", as.hash(), ag.filter.parentElement.insertAdjacentHTML("beforeend", f.get_svg_icon("search")), aj.dropdown(), _c.has_login) {
      ag.topbar_top.insertAdjacentHTML("beforeend", '<a href="' + location.href.split("?")[0] + '?logout" class="btn-icon btn-topbar" id="logout"' + O("logout", !0) + ">" + f.get_svg_icon("logout") + "</a>");
      var a = ag.topbar_top.lastElementChild;
      s(a, function(b) {
        b.preventDefault(), ap.fire(aj.get("logout", !0) + "?").then(b => {
          b.isConfirmed && location.assign(a.href)
        })
      })
    }
    screenfull.isEnabled && (ag.topbar_top.insertAdjacentHTML("beforeend", '<button class="btn-icon btn-topbar" id="topbar-fullscreen">' + f.get_svg_icon_multi("expand", "collapse") + "</button>"), s(ag.topbar_top.lastElementChild, function() {
      screenfull.toggle()
    }), screenfull.on("change", function() {
      document.documentElement.classList.toggle("is-fullscreen", screenfull.isFullscreen)
    })), f.topbar_info = function(a, b) {
      ag.topbar_info.className = "info-" + b, ag.topbar_info.innerHTML = a
    }, f.topbar_info_search = function(b, a) {
      if (W(ag.sortbar, !a), !b) return ag.topbar_info.className = "info-hidden";
      ag.topbar_info.classList.contains("info-search") ? (ag.topbar_info.classList.toggle("info-nomatch", !a), ag.topbar_info.children[0].textContent = a, ag.topbar_info.children[2].textContent = b) : f.topbar_info('<span class="info-search-count">' + a + '</span><span class="info-search-lang"><span data-lang="matches found for">' + aj.get("matches found for") + '</span></span><span class="info-search-phrase">' + b + '</span><button class="info-search-reset" data-action="reset">' + f.get_svg_icon("close") + "</button>", "search" + (a ? "" : " info-nomatch"))
    }
  }(), _c.config.favicon && document.head.insertAdjacentHTML("beforeend", _c.config.favicon), "IntersectionObserver" in window && "IntersectionObserverEntry" in window && "intersectionRatio" in window.IntersectionObserverEntry.prototype ? q() : f.load_plugin("intersection-observer", q, {
    src: ["intersection-observer@0.12.0/intersection-observer.js"]
  })
}("undefined" == typeof files ? files = {} : files)