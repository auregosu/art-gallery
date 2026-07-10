// Animated background
(function () {
  var canvas = document.getElementById("bg-canvas");
  if (!canvas) return;
  var ctx = canvas.getContext("2d");

  var PAINTINGS_DIR = "gallery/paintings/";
  var MAX_IMAGES = 10;
  var HOLD_MS = 5000;
  var FADE_MS = 2500;
  var OVERSCALE = 1.15;
  var PARALLAX = 40;
  var EASING = 0.05;

  var images = []; // loaded Image objects
  var currentIdx = 0;
  var nextIdx = 1;
  var phaseStart = 0; // timestamp the current hold/fade began
  var fading = false;

  // Parallax target mouse position
  var targetX = 0,
    targetY = 0; // normalized -1..1
  var actualX = 0,
    actualY = 0;

  function resize() {
    var dpr = window.devicePixelRatio || 1;
    canvas.width = Math.floor(canvas.clientWidth * dpr);
    canvas.height = Math.floor(canvas.clientHeight * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  }
  window.addEventListener("resize", resize);

  window.addEventListener("mousemove", function (ev) {
    targetX = (ev.clientX / window.innerWidth) * 2 - 1; // -1 .. 1
    targetY = (ev.clientY / window.innerHeight) * 2 - 1;
  });

  // Shuffle, then take the first MAX_IMAGES.
  function pickRandom(arr, count) {
    var a = arr.slice();
    for (var i = a.length - 1; i > 0; i--) {
      var j = Math.floor(Math.random() * (i + 1));
      var t = a[i];
      a[i] = a[j];
      a[j] = t;
    }
    return a.slice(0, count);
  }

  function loadImage(src) {
    return new Promise(function (resolve) {
      var img = new Image();
      img.onload = function () {
        resolve(img);
      };
      img.onerror = function () {
        resolve(null);
      }; // skip broken ones
      img.src = src;
    });
  }

  function drawImage(img, alpha) {
    if (!img) return;
    var cw = canvas.clientWidth,
      ch = canvas.clientHeight;

    // Cover the whole canvas
    var scale = Math.max(cw / img.width, ch / img.height) * OVERSCALE;
    var dw = img.width * scale;
    var dh = img.height * scale;

    // Center, then shift by the eased parallax offset
    var dx = (cw - dw) / 2 + actualX * PARALLAX;
    var dy = (ch - dh) / 2 + actualY * PARALLAX;

    ctx.globalAlpha = alpha;
    ctx.drawImage(img, dx, dy, dw, dh);
    ctx.globalAlpha = 1;
  }

  // Animation loop
  function frame(now) {
    if (!phaseStart) phaseStart = now;

    // Ease the parallax offset toward the mouse target.
    actualX += (targetX - actualX) * EASING;
    actualY += (targetY - actualY) * EASING;

    var elapsed = now - phaseStart;

    ctx.clearRect(0, 0, canvas.clientWidth, canvas.clientHeight);

    if (!fading) {
      // Waiting
      drawImage(images[currentIdx], 1);
      if (elapsed >= HOLD_MS && images.length > 1) {
        fading = true;
        phaseStart = now;
      }
    } else {
      // Crossfading
      var t = Math.min(elapsed / FADE_MS, 1);
      drawImage(images[currentIdx], 1);
      drawImage(images[nextIdx], t);
      if (t >= 1) {
        currentIdx = nextIdx;
        nextIdx = (nextIdx + 1) % images.length;
        fading = false;
        phaseStart = now;
      }
    }

    requestAnimationFrame(frame);
  }

  // Setup
  fetch("scripts/paintings_json.php")
    .then(function (r) {
      return r.json();
    })
    .then(function (paths) {
      if (!Array.isArray(paths) || paths.length === 0) return;
      var chosen = pickRandom(paths, MAX_IMAGES);
      return Promise.all(
        chosen.map(function (p) {
          return loadImage(PAINTINGS_DIR + p);
        }),
      );
    })
    .then(function (loaded) {
      if (!loaded) return;
      images = loaded.filter(Boolean);
      if (images.length === 0) return;
      nextIdx = images.length > 1 ? 1 : 0;
      resize();
      requestAnimationFrame(frame);
    })
    .catch(function () {
      // Fails, background stays blank
    });
})();
