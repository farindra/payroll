<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                Selamat Datang, {{ Auth::user()->name }}
            </h2>
            <p class="text-gray-600 dark:text-gray-400">
                Portal Employee - Sistem Informasi Penggajian
            </p>
        </div>

        <!-- Employee Information -->
        @if (Auth::user()->employee)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Informasi Pribadi
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Nama Lengkap</p>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ Auth::user()->employee->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">NIK</p>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ Auth::user()->employee->nip }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Departemen</p>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ Auth::user()->employee->department->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Jabatan</p>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ Auth::user()->employee->position }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Payroll Reports Card -->
            <a href="{{ route('filament.employee.pages.employee-payroll-reports') }}"
               class="block bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Laporan Penggajian</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Lihat laporan gaji dan kehadiran Anda</p>
                    </div>
                </div>
            </a>

            <!-- Profile Card -->
            <a href="{{ route('filament.employee.auth.profile') }}"
               class="block bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Profil</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Kelola informasi pribadi Anda</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Information Section -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
            <h3 class="text-lg font-medium text-blue-900 dark:text-blue-100 mb-2">
                Informasi Penting
            </h3>
            <ul class="space-y-2 text-sm text-blue-800 dark:text-blue-200">
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Anda hanya dapat mengakses menu Laporan Penggajian di portal ini</span>
                </li>
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Semua data yang ditampilkan bersifat pribadi dan terbatas untuk akun Anda</span>
                </li>
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Hubungi HRD jika Anda menemukan kesalahan pada data penggajian</span>
                </li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>
