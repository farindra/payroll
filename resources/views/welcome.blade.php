@extends('layouts.app')

@section('content')
<header class="hero-section">
    <div class="container">
        <div class="logo mb-2">
            <img src="/images/logo.svg" alt="" height="70">
        </div>
        <h1>Automatisasi Gaji dengan Mudah dan Cepat</h1>
        <p class="lead">Kelola penggajian karyawan Anda dengan solusi aplikasi payroll kami yang intuitif, akurat, dan aman. Hemat waktu, kurangi kesalahan, fokus pada yang terpenting.</p>
        <a href="/admin" class="btn btn-warning btn-lg fw-bold px-5 py-3">Login Admin</a>
        <a href="/employee" class="btn btn-warning btn-lg fw-bold px-5 py-3">Login Karyawan</a>
    </div>
</header>

<section id="fitur" class="feature-section">
    <div class="container">

        <h2 class="text-center mb-5 fw-bold display-5">Fitur Unggulan Kami</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

            <div class="col">
                <div class="feature-card text-center">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Penghitungan Gaji Otomatis</h3>
                    <p class="text-muted">Hitung gaji, pajak, dan tunjangan secara akurat dan otomatis, setiap bulan.</p>
                </div>
            </div>

            <div class="col">
                <div class="feature-card text-center">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Slip Gaji Digital</h3>
                    <p class="text-muted">Kirim slip gaji elektronik langsung ke karyawan Anda dengan aman.</p>
                </div>
            </div>

            <div class="col">
                <div class="feature-card text-center">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Laporan & Analitik</h3>
                    <p class="text-muted">Dapatkan laporan penggajian komprehensif untuk pengambilan keputusan yang lebih baik.</p>
                </div>
            </div>

            <div class="col">
                <div class="feature-card text-center">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Manajemen Absensi</h3>
                    <p class="text-muted">Integrasi mudah dengan sistem absensi untuk perhitungan gaji yang tepat.</p>
                </div>
            </div>

            <div class="col">
                <div class="feature-card text-center">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Keamanan Data Terjamin</h3>
                    <p class="text-muted">Data penggajian Anda dilindungi dengan standar keamanan tertinggi.</p>
                </div>
            </div>

            <div class="col">
                <div class="feature-card text-center">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-cloud-arrow-up"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Berbasis Cloud</h3>
                    <p class="text-muted">Akses aplikasi dari mana saja, kapan saja, tanpa instalasi yang rumit.</p>
                </div>
            </div>

        </div>

    </div>
</section>




@endsection
