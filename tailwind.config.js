/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./resources/views/livewire/**/*.blade.php", // Tambahkan ini
        "./vendor/rappasoft/laravel-livewire-tables/resources/views/**/*.blade.php",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: [
                    '"Instrument Sans"',
                    "ui-sans-serif",
                    "system-ui",
                    "sans-serif",
                    '"Apple Color Emoji"',
                    '"Segoe UI Emoji"',
                    '"Segoe UI Symbol"',
                    '"Noto Color Emoji"',
                ],
            },
        },
    },
    plugins: [],
};
