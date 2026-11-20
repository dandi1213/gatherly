import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                canvas: '#FAF9F6',
                surface: '#FFFFFF',
                ink: '#18181B',
                muted: '#52525B',
                line: '#E4E4E7',
                primary: {
                    50: '#FDF2EF',
                    100: '#F9E1DC',
                    200: '#F3C5BC',
                    300: '#EBA295',
                    400: '#E57C6B',
                    500: '#E05A47',
                    600: '#C84B39',
                    700: '#A93C2D',
                    800: '#8A3226',
                    900: '#722A21',
                },
                success: {
                    50: '#F0F7F2',
                    100: '#DDEEE2',
                    200: '#C3E0CC',
                    300: '#9CCBA9',
                    400: '#6FAF81',
                    500: '#4C9162',
                    600: '#3F7D58',
                    700: '#2F5E42',
                    800: '#274B36',
                    900: '#203E2D',
                },
                warning: {
                    50: '#FDF6E9',
                    100: '#F9EACB',
                    200: '#F1DCAA',
                    300: '#E7C780',
                    400: '#D8AA51',
                    500: '#BE8A2F',
                    600: '#A06A1F',
                    700: '#7F521A',
                    800: '#674218',
                    900: '#553716',
                },
                danger: {
                    50: '#FBEFED',
                    100: '#F7DCD8',
                    200: '#F1C1BA',
                    300: '#E79C91',
                    400: '#D96F61',
                    500: '#C45244',
                    600: '#B24136',
                    700: '#8F2F26',
                    800: '#76291F',
                    900: '#5F241C',
                },
            },
            boxShadow: {
                subtle: '0 1px 2px 0 rgb(24 24 27 / 0.04)',
                lift: '0 8px 24px -16px rgb(24 24 27 / 0.28)',
            },
            letterSpacing: {
                editorial: '-0.025em',
            },
        },
    },

    plugins: [forms],
};
