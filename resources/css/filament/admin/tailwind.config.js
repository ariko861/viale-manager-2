import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/asantibanez/**/*.blade.php',
    ],
    safelist: [
        {
            pattern: /bg-(red|green|yellow|emerald)-(100)/,
            variants: ['lg', 'hover', 'focus', 'lg:hover'],
        },
    ]
}
