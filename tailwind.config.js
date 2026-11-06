import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'ice-white': '#F1F5F9',
                navy: {
                    DEFAULT: '#0B132B',
                    700: '#131D3B',
                    600: '#1C2849',
                    400: '#5B6785',
                    200: '#CBD5E1',
                },
                cyan: {
                    DEFAULT: '#00F2FE',
                    600: '#00C4CE',
                    100: '#D6FDFF',
                    glow: 'rgba(0, 242, 254, 0.35)',
                },
            },
            boxShadow: {
                card: '0 1px 2px rgba(11, 19, 43, 0.04), 0 4px 16px rgba(11, 19, 43, 0.06)',
                pop: '0 8px 24px rgba(11, 19, 43, 0.16)',
            }
        },
    },

    plugins: [forms],
};