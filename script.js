// script.js
document.addEventListener("DOMContentLoaded", function() {
    const container = document.querySelector(".container");
    container.style.opacity = 0;

    let opacity = 0;
    const fadeIn = setInterval(() => {
        opacity += 0.02;
        container.style.opacity = opacity;

        if(opacity >= 1){
            clearInterval(fadeIn);
        }
    }, 20);
});
