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
        PINK: "#9D8099",
      },
    },
  },
  plugins: [],
}
