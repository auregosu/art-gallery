// Lightbox for the single painting page
var img = document.getElementById("paintingImage");
var box = document.getElementById("lightbox");
var big = document.getElementById("lightboxImg");

img.addEventListener("click", function () {
  box.classList.add("open");
});

// Toggle 2x zoom
big.addEventListener("click", function (e) {
  e.stopPropagation();
  big.classList.toggle("zoomed");
});

big.addEventListener("mousemove", function (e) {
  if (!big.classList.contains("zoomed")) return;
  var rect = big.getBoundingClientRect();
  var x = ((e.clientX - rect.left) / rect.width) * 100;
  var y = ((e.clientY - rect.top) / rect.height) * 100;
  big.style.transformOrigin = x + "% " + y + "%";
});

// Click the dark background to close
box.addEventListener("click", function () {
  box.classList.remove("open");
  big.classList.remove("zoomed");
});

document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") {
    box.classList.remove("open");
    big.classList.remove("zoomed");
  }
});
