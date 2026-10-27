/**
 * Tailwind v4 compatibility config: design tokens live here and are mirrored
 * by CSS custom properties in resources/css/input.css for component styles.
 * Existing utility names remain mapped to the restrained palette so legacy
 * templates can be refreshed incrementally without breaking the build.
 */
const slate = {
  50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1', 400: '#94a3b8',
  500: '#64748b', 600: '#475569', 700: '#334155', 800: '#1e293b', 900: '#0f172a', 950: '#020617',
};
const indigo = {
  50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc', 400: '#818cf8',
  500: '#4f46e5', 600: '#4338ca', 700: '#3730a3', 800: '#312e81', 900: '#1e1b4b',
};
const amber = {
  50: '#fffbeb', 100: '#fef3c7', 200: '#fde68a', 300: '#fcd34d', 400: '#fbbf24',
  500: '#f59e0b', 600: '#d97706', 700: '#b45309', 800: '#92400e', 900: '#78350f',
};
const green = {
  50: '#f0fdf4', 100: '#dcfce7', 200: '#bbf7d0', 300: '#86efac', 400: '#4ade80',
  500: '#22c55e', 600: '#16a34a', 700: '#15803d', 800: '#166534', 900: '#14532d',
};
const red = {
  50: '#fef2f2', 100: '#fee2e2', 200: '#fecaca', 300: '#fca5a5', 400: '#f87171',
  500: '#ef4444', 600: '#dc2626', 700: '#b91c1c', 800: '#991b1b', 900: '#7f1d1d',
};

module.exports = {
  content: ['./app/views/**/*.php', './public/assets/js/**/*.js'],
  theme: {
    extend: {
      colors: {
        slate,
        primary: indigo,
        accent: amber,
        success: green,
        danger: red,
        warning: amber,
        info: indigo,
        // Backward-compatible names, intentionally reduced to the same hues.
        navy: slate,
        electric: indigo,
        violet: indigo,
        brand: indigo,
        gold: amber,
        flame: red,
        jade: green,
        surface: { DEFAULT: '#ffffff', muted: '#f8fafc', sunken: '#f1f5f9' },
        ink: { DEFAULT: '#0f172a', muted: '#64748b' },
        line: '#e2e8f0',
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        display: ['Manrope', 'Inter', 'ui-sans-serif', 'sans-serif'],
      },
      fontSize: {
        '2xs': ['0.6875rem', { lineHeight: '1rem' }],
      },
      spacing: {
        4.5: '1.125rem', 5.5: '1.375rem', 18: '4.5rem', 22: '5.5rem', 30: '7.5rem',
      },
      borderRadius: {
        control: '0.625rem', panel: '1rem', card: '1.25rem',
      },
      boxShadow: {
        card: '0 1px 2px rgb(15 23 42 / 0.04), 0 1px 3px rgb(15 23 42 / 0.06)',
        lift: '0 12px 28px -12px rgb(15 23 42 / 0.18)',
      },
      transitionDuration: { fast: '150ms', base: '220ms' },
    },
  },
};
