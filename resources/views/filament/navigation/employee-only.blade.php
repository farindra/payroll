@php
    use Illuminate\Support\Facades\Auth;

    $user = Auth::user();
    $isEmployee = !$user->hasRole('admin');
@endphp

@if ($isEmployee)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Hide all navigation items except Dashboard and Payroll Reports
            const navItems = document.querySelectorAll('[x-data] nav a');
            navItems.forEach(item => {
                const href = item.getAttribute('href');
                const text = item.textContent.toLowerCase();

                // Only allow dashboard and payroll reports
                if (href && !href.includes('/dashboard') && !href.includes('/employee-payroll-reports')) {
                    item.closest('li, div').style.display = 'none';
                }
            });

            // Also hide navigation groups that might contain other items
            const navGroups = document.querySelectorAll('[x-data] .fi-nav-group');
            navGroups.forEach(group => {
                const groupText = group.textContent.toLowerCase();
                if (!groupText.includes('dashboard') && !groupText.includes('laporan')) {
                    group.style.display = 'none';
                }
            });
        });
    </script>
@endif