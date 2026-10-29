{{-- The plain layout the packages page uses when the host app has not configured one of its own. --}}
@props(['title' => 'Packages'])
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }}</title>
    <style>
        body {
            margin: 0;
            padding: 2rem 1rem;
            background: #fff;
            color: #1f2937;
            font: 16px/1.5 system-ui, sans-serif;
        }
    </style>
</head>

<body>
    {{ $slot }}
</body>

</html>
