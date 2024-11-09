<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite('resources/css/app.css')

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
        integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
        crossorigin="" />


    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
    </style>
</head>

<body class="antialiased">
    <div
        class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">
        @if (Route::has('filament.admin.auth.login'))
            <div class="fixed top-0 right-0 px-6 py-4">
                @auth
                    <a href="{{ \Filament\Pages\Dashboard::getUrl() }}"
                        class="text-sm text-gray-700 dark:text-gray-500 underline">Dashboard</a>
                @else
                    <a href="{{ route('filament.admin.auth.login') }}" class="text-sm text-gray-700 dark:text-gray-500 underline">Log in</a>
                @endauth
            </div>
        @endif

        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-center pt-8 sm:justify-start sm:pt-0">
                <x-logo.viale-manager height=150 width=180 class="block h-9 w-auto" />
                <h1 class="">{{ env('APP_NAME') }}</h1>
            </div>

            <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
                @if (@session('error'))
                    <p class="alert alert-error"> {{ session('error') }}</p>
                @endif
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="ml-4 text-lg leading-7 font-semibold">
                                <h3 class="underline text-gray-900 dark:text-white">{{ __('Contactez nous') }}</h3>
                            </div>
                        </div>
                        @php
                            $text_class = 'mt-2';
                        @endphp
                        <div class="ml-12">
                            <div class="{{ $text_class }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline align-middle"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <a class="align-middle"
                                    href="mailto:{{ $options->firstWhere('name', 'email')->value ?? '' }}">{{ $options->firstWhere('name', 'email')->value ?? '' }}</a>
                            </div>
                            <div class="{{ $text_class }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline align-middle"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                {{ $options->firstWhere('name', 'phone')->value ?? '' }}
                            </div>
                            <div class="{{ $text_class }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline align-middle"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ $options->firstWhere('name', 'address')->value ?? '' }}
                            </div>
                            <div class="w-96 h-48"></div>
                        </div>
                    </div>

                    <div
                        class="p-6 border-t border-gray-200 dark:border-gray-700 md:border-t-0 md:border-l embed-responsive embed-responsive-16by9">
                        <div id="map" class="h-full w-full embed-responsive-item"></div>
                    </div>

                </div>
            </div>

{{--            ##### TRANSPORTS--}}
{{--            <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">--}}
{{--                <div class="flex items-center">--}}
{{--                    <div class="p-6">--}}
{{--                        <div class="ml-4 text-lg leading-7 font-semibold">--}}
{{--                            <h3 class="underline text-gray-900 dark:text-white">{{ __('Rechercher un transport') }}--}}
{{--                            </h3>--}}
{{--                            @if ($errors->any())--}}
{{--                                <div class="alert alert-danger">--}}
{{--                                    <ul>--}}
{{--                                        @foreach ($errors->all() as $error)--}}
{{--                                            <li>{{ $error }}</li>--}}
{{--                                        @endforeach--}}
{{--                                    </ul>--}}
{{--                                </div>--}}
{{--                            @endif--}}
{{--                            <form method="POST">--}}
{{--                                @method('POST')--}}
{{--                                @csrf--}}
{{--                                <p>Si vous recherchez un véhicule pour venir à {{ config('app.name', 'Laravel') }},--}}
{{--                                    saisissez l'email que vous avez utilisé pour nous contacter, nous vous enverrons une--}}
{{--                                    liste de transports disponibles</p>--}}
{{--                                <p><input type="email" name="email" /></p>--}}
{{--                                <button type="submit">Rechercher des transports</button>--}}
{{--                            </form>--}}
{{--                            @if ($email ?? '')--}}
{{--                                @if ($email =='error')--}}
{{--                                    <p>{{ __("Nous n'avons pas trouvé ce mail dans la liste de nos réservations") }}</p>--}}
{{--                                @else--}}
{{--                                    <p>{{ __("Nous avons bien trouvé l'email :mail, vous devriez recevoir un mail avec la liste des places disponibles", ['mail' => $email ?? '']) }}</p>--}}
{{--                                @endif--}}
{{--                            @endif--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            FIN TRANSPORTS--}}

            <div class="flex justify-center mt-4 sm:items-center sm:justify-between">
                <div class="text-center text-sm text-gray-500 sm:text-left">
                    <div class="flex items-center">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <title>GitHub</title>
                            <path
                                d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12" />
                        </svg>

                        <a href="https://github.com/ariko861/viale-manager-2" class="ml-1 underline">
                            Code Source
                        </a>

                    </div>
                </div>

                <div class="ml-4 text-center text-sm text-gray-500 sm:text-right sm:ml-0">
                    Viale-manager {{ config('app.version') }}
                </div>
            </div>
        </div>
    </div>
    <script>
        var map;
        var coordinates = {
            lat: {{ env('MAP_LATITUDE', 0) }},
            long: {{ env('MAP_LONGITUDE', 0) }},
        };
        var access_token = "{{ env('MAPBOX_TOKEN', '') }}";

        function initMap() {
            var myLatLng = [coordinates.lat, coordinates.long];

            var map = L.map('map').setView(myLatLng, 13);

            var marker = L.marker(myLatLng).addTo(map);

            L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=' + access_token, {
                attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
                maxZoom: 18,
                id: 'mapbox/streets-v11',
                tileSize: 512,
                zoomOffset: -1,
                accessToken: access_token
            }).addTo(map);

        }
    </script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
        integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
        crossorigin=""></script>
    <script>
        initMap();
    </script>

</body>

</html>
