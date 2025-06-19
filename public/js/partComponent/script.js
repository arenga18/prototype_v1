$(document).on("click", "button", function (e) {
    if (this.id !== "key-bindings-2") {
        e.preventDefault();
    }
});

const fullscreenBtn = document.getElementById("fullscreen-btn");
const container = document.querySelector(
    'div[style="width: 100%; position: relative;"]'
);

fullscreenBtn.addEventListener("click", function () {
    console.log(fullscreenBtn);
    container.classList.toggle("fullscreen-mode");

    const isFullscreen = container.classList.contains("fullscreen-mode");

    if (isFullscreen) {
        fullscreenBtn.innerHTML = `
            Exit fullscreen
          `;
    } else {
        fullscreenBtn.innerHTML = `
           Fullscreen
          `;
    }

    // Trigger resize event untuk Univer agar menyesuaikan ukuran
    setTimeout(() => {
        window.dispatchEvent(new Event("resize"));
    }, 100);
});
