import preset from './vendor/filament/support/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/asantibanez/**/*.blade.php'
    ],
    safelist: [
        'bg-emerald-100',
        'bg-yellow-100',
    ]
}
