// Simple slideshow for the main page
(function () {
  var track = document.getElementById("slidesTrack");
  var slides = document.querySelectorAll("#slideshow .slide");
  if (!track || slides.length === 0) return;
  var current = 0;
  var timer = null;

  function show(index) {
    current = (index + slides.length) % slides.length;
    track.style.transform = "translateX(" + -current * 100 + "%)";
  }

  function next() {
    show(current + 1);
  }
  function prev() {
    show(current - 1);
  }

  function start() {
    timer = setInterval(next, 4000);
  }
  function stop() {
    clearInterval(timer);
  }

  var nextBtn = document.getElementById("nextSlide");
  var prevBtn = document.getElementById("prevSlide");

  if (nextBtn)
    nextBtn.addEventListener("click", function () {
      stop();
      next();
      start();
    });
  if (prevBtn)
    prevBtn.addEventListener("click", function () {
      stop();
      prev();
      start();
    });

  start();
})();
