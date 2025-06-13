$(document).ready(function () {
    // Konversi HTMLCollection ke Array lalu looping
    const buttons = document.getElementsByTagName("button");
    Array.from(buttons).forEach((button) => {
        button.addEventListener("click", function (e) {
            if (button.id !== "key-bindings-2") {
                e.preventDefault();
            }
        });
    });
});
