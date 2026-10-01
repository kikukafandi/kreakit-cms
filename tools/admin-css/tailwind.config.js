/**
 * Builds public/assets/admin/admin.css for the admin panel and installer.
 * Only maintainers need this; buyers get the compiled CSS and never need Node.
 *   npx tailwindcss@3 -c tools/admin-css/tailwind.config.js -i tools/admin-css/input.css -o public/assets/admin/admin.css --minify
 */
module.exports = {
  content: ['./public/admin/**/*.php', './public/install/**/*.php', './app/Helpers/**/*.php'],
  theme: {
    extend: {
      fontFamily: { sans: ['Manrope', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
      fontWeight: { black: '800' },
      borderRadius: { '2xl': '0.875rem', '3xl': '1.125rem' },
      colors: {
        brand: { 50: '#f0fdfa', 100: '#ccfbf1', 200: '#99f6e4', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e', 800: '#115e59', 900: '#134e4a', 950: '#042f2e' },
      },
    },
  },
};
