<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Air App</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body>

    <div style="padding: 40px;">
        <h1>Test Livewire</h1>

        @livewire('counter')
        @livewire('service-manager')
    </div>

    @livewireScripts
</body>
</html>