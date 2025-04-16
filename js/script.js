let slides = document.querySelectorAll(".slide");
let index = 0;

function showSlide() {
    // Hide all slides
    slides.forEach(slide => slide.style.opacity = 0);

    // Show the current slide
    slides[index].style.opacity = 1;

    // Move to the next slide
    index = (index + 1) % slides.length;
}

// Change slide every 3 seconds
setInterval(showSlide, 3000);

// Show first slide initially
showSlide();

function showCategory(category) {
    document.getElementById("bikes").style.display = "none";
    document.getElementById("scooters").style.display = "none";
    document.getElementById("ev").style.display = "none";

    document.getElementById(category).style.display = "flex";
}

