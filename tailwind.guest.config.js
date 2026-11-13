// Tailwind config for the pre-built stylesheet of the guest (login) layout: public/css/tw-guest.css
// The guest layout uses Tailwind's defaults (it has no inline `tailwind.config`).
// Rebuild after changing any view:  php artisan css:build

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],

    theme: {
        extend: {},
    },

    plugins: [],
};
