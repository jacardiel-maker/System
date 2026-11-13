// Tailwind config for the pre-built stylesheet of the main (logged-in) layout: public/css/tw-app.css
// Must stay the same as the inline `tailwind.config` in resources/views/layouts/app.blade.php (the CDN fallback).
// Rebuild after changing any view:  php artisan css:build

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            },
            colors: {
                primary: {
                    50: '#fff7ed',
                    100: '#ffedd5',
                    200: '#fed7aa',
                    300: '#fdba74',
                    400: '#fb923c',
                    500: '#f97316',
                    600: '#ea580c',
                    700: '#c2410c',
                    800: '#9a3412',
                    900: '#7c2d12',
                },
                slate: {
                    850: '#1e293b',
                },
            },
        },
    },

    plugins: [],
};
