/** @type {import('tailwindcss').Config} */
export default {
  // Tambahkan baris ini
  darkMode: 'class', 

  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}