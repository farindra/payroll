<footer class="bg-gray-800 text-white py-6 mt-auto">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center">
            {{-- <div class="mb-4 md:mb-0">
                <p class="text-sm text-gray-300">
                    &copy; {{ date('Y') }} Payroll System. All rights reserved.
                </p>
            </div> --}}
            <div class="text-center md:text-right" style="color: gray">
                <p class="">
                    Powered with ❤️ By Farindra Project <span class="text-red-500"></span>
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
    footer {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        z-index: 1000;
    }

    /* Adjust main content to prevent overlap with fixed footer */
    body {
        padding-bottom: 80px;
    }
</style>
