<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Payroll System') }}</title>
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Scripts -->
    @vite('resources/css/app.css')
    {{-- @livewireStyles --}}
    <style>
        :root {
            --primary-blue: #007bff; /* Biru terang Bootstrap */
            --dark-blue: #0056b3; /* Biru gelap */
            --light-blue: #e0f2ff; /* Biru sangat terang untuk latar belakang */
            --text-dark: #343a40; /* Teks gelap */
            --text-light: #f8f9fa; /* Teks terang */
        }

        body {
            font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--text-dark);
        }

        .hero-section {
            /* Latar belakang gambar realistis */
            background-image: url("https://boardroomlimited.com.au/wp-content/uploads/2024/03/What-Are-the-Main-Steps-Involved-in-Payroll-Processing-Web-Banner.png"); /* Gambar kantor modern */
            background-size: cover;
            background-position: center;
            background-attachment: fixed; /* Parallax effect */
            color: var(--text-light);
            padding: 10rem 0;
            text-align: center;
            position: relative; /* Penting untuk overlay */
            overflow: hidden; /* Mencegah background overlay melebihi batas */
        }

        /* Overlay semi-transparan untuk teks agar lebih mudah dibaca */
        .hero-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 50, 100, 0.7); /* Biru gelap semi-transparan */
            z-index: 1;
        }

        .hero-section .container {
            position: relative; /* Menempatkan konten di atas overlay */
            z-index: 2;
        }

        .hero-section h1 {
            font-weight: 700;
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); /* Memberi efek shadow pada teks */
        }

        .hero-section p.lead {
            font-size: 1.25rem;
            margin-bottom: 3rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.4);
        }

        .feature-section {
            padding: 6rem 0;
            background-color: var(--light-blue);
        }

        .feature-icon-wrapper {
            font-size: 3rem;
            color: var(--primary-blue);
            margin-bottom: 1rem;
        }

        .feature-card {
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
            padding: 2rem;
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease; /* Efek hover */
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.12);
        }

        .footer-section {
            background-color: var(--dark-blue);
            color: var(--text-light);
            padding: 3rem 0;
            text-align: center;
        }

        .footer-section a {
            color: var(--text-light);
            text-decoration: none;
            margin: 0 0.5rem;
        }

        .footer-section a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body class="font-sans antialiased">
        @yield('content')

        <!-- Footer Component -->
        @include('components.footer')

    {{-- @livewireScripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @vite('resources/js/app.js')
</body>
</html>
