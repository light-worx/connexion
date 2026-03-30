/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/views/**/*.blade.php',
        './resources/views/filament/**/*.blade.php',
        './app/Filament/**/*.php',
        './vendor/filament/**/*.blade.php',
        './Modules/**/resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {},
    },
    plugins: [],
}