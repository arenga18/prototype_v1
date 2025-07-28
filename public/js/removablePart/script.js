$(document).on("click", "button", function (e) {
    if (this.id !== "key-bindings-2" && this.id !== "key-bindings-1") {
        e.preventDefault();
    }
});

$("#modulReference").select2({
    placeholder: "--Pilih Modul--",
    allowClear: true,
    width: "100%",
});

$("#modulSelect").select2({
    placeholder: "--Pilih Modul--",
    allowClear: true,
    width: "100%",
});

// const fullscreenBtn = document.getElementById("fullscreen-btn");
// const container = document.querySelector(
//     'div[style="width: 100%; position: relative;"]'
// );

// fullscreenBtn.addEventListener("click", function () {
//     console.log(fullscreenBtn);
//     container.classList.toggle("fullscreen-mode");

//     const isFullscreen = container.classList.contains("fullscreen-mode");

//     if (isFullscreen) {
//         fullscreenBtn.innerHTML = `
//             Exit
//           `;
//     } else {
//         fullscreenBtn.innerHTML = `
//             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
//               <path d="M1.5 1a.5.5 0 0 0-.5.5v4a.5.5 0 0 1-1 0v-4A1.5 1.5 0 0 1 1.5 0h4a.5.5 0 0 1 0 1h-4zM10 .5a.5.5 0 0 1 .5-.5h4A1.5 1.5 0 0 1 16 1.5v4a.5.5 0 0 1-1 0v-4a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 1-.5-.5zM.5 10a.5.5 0 0 1 .5.5v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 0 14.5v-4a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v4a1.5 1.5 0 0 1-1.5 1.5h-4a.5.5 0 0 1 0-1h4a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 1 .5-.5z"/>
//             </svg>
//           `;
//     }

//     // Trigger resize event untuk Univer agar menyesuaikan ukuran
//     setTimeout(() => {
//         window.dispatchEvent(new Event("resize"));
//     }, 100);
// });
