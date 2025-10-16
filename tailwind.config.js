import defaultTheme from "tailwindcss/defaultTheme";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./vendor/filament/**/*.blade.php", // ⚠️ Quan trọng: để Tailwind áp dụng trong Filament
    ],
    darkMode: "class", // ⚠️ Đảm bảo dùng class để có thể chủ động control dark mode
    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [
        // require("@tailwindcss/forms"), // (optional nhưng thường dùng)
        // require("@tailwindcss/typography"), // (optional nhưng tốt cho nội dung)
    ],
};
