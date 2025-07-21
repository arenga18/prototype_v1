$(document).on("click", "button", function (e) {
    if (this.id !== "key-bindings-2" && this.id !== "key-bindings-1") {
        e.preventDefault();
    }
});

$("#modulModal").on("shown.bs.modal", function () {
    $("#modulSelect").select2({
        placeholder: "--Pilih Modul--",
        allowClear: true,
        width: "100%",
        dropdownParent: $("#modulModal"),
    });
});

// Inisialisasi Select2 untuk part
$("#partModal").on("shown.bs.modal", function () {
    $("#partSelect").select2({
        placeholder: "--Pilih Modul--",
        allowClear: true,
        width: "100%",
        dropdownParent: $("#partModal"),
    });
});

const fullscreenBtn = document.getElementById("fullscreen-btn");
const container = document.querySelector(
    'div[style="width: 100%; position: relative;"]'
);

fullscreenBtn.addEventListener("click", function () {
    console.log(fullscreenBtn);
    container.classList.toggle("fullscreen-mode");

    // Update icon dan teks tombol
    const icon = fullscreenBtn.querySelector("svg");
    const isFullscreen = container.classList.contains("fullscreen-mode");

    if (isFullscreen) {
        fullscreenBtn.innerHTML = `
            Exit
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
