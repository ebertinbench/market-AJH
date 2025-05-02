/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      colors: {
        BLACK: "#000000",
        VIOLET: "#291527",
        GRAY: "#393D3A",
        WHITE: "#EEECF2",
        GREEN: "#585B4C",
      },
    },
  },
  plugins: [],
}
