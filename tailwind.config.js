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
            colors: {
                navy: {
                    DEFAULT: '#0F172A', // Slate 900
                    900: '#0F172A',
                    800: '#1E293B',
                    700: '#334155',
                    600: '#475569',
                    400: '#64748B',
                    300: '#94A3B8',
                    200: '#E2E8F0',
                    100: '#F1F5F9',
                    50: '#F8FAFC',
                },
                brand: {
                    DEFAULT: '#2563EB', // Modern Royal Blue
                    600: '#1D4ED8',
                    700: '#1E40AF',
                    500: '#3B82F6',
                    100: '#DBEAFE',
                    50: '#EFF6FF',
                },
                ice: '#F8FAFC',
                success: {
                    DEFAULT: '#16A34A',
                    bg: '#DCFCE7',
                    text: '#15803D',
                },
                warning: {
                    DEFAULT: '#D97706',
                    bg: '#FEF3C7',
                    text: '#B45309',
                },
                danger: {
                    DEFAULT: '#DC2626',
                    bg: '#FEE2E2',
                    text: '#B91C1C',
                },
            },
            fontFamily: {
<<<<<<< HEAD
                sans: ['Plus Jakarta Sans', 'Inter', ...defaultTheme.fontFamily.sans],
=======
                sans: ['Poppins', 'system-ui', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'sans-serif'],
            },
            borderRadius: {
                sm: '4px',
                DEFAULT: '6px',
                md: '6px',
                lg: '8px',
                xl: '10px',
                '2xl': '12px',
            },
            boxShadow: {
                card: '0 1px 3px rgba(15, 23, 42, 0.04), 0 4px 12px rgba(15, 23, 42, 0.03)',
                lift: '0 14px 28px -6px rgba(15, 23, 42, 0.08), 0 6px 14px -3px rgba(15, 23, 42, 0.04)',
                pop: '0 10px 30px rgba(15, 23, 42, 0.08)',
>>>>>>> dc048bd ( fitur lengkap)
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