<x-filament-panels::page>
    <x-filament-panels::form wire:submit="processPayroll">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </x-filament-panels::form>

    @if ($isProcessing)
        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center">
                <x-filament::loading-indicator class="h-4 w-4 text-blue-600" />
                <span class="ml-2 text-blue-800 font-medium">Processing payroll...</span>
            </div>
            <p class="mt-2 text-sm text-blue-700">
                Payroll calculation is in progress. This may take a few minutes depending on the number of employees.
            </p>
        </div>
    @endif

    @if ($processResults)
        <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Processing Results</h3>

            @if ($processResults['success'])
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <h4 class="ml-2 text-green-800 font-medium">Payroll Processed Successfully</h4>
                    </div>
                    <p class="mt-2 text-green-700">{{ $processResults['message'] }}</p>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white p-3 rounded border border-green-200">
                            <div class="text-lg font-semibold text-green-700">{{ $processResults['processed_employees'] ?? 0 }}</div>
                            <div class="text-sm text-green-600">Employees Processed</div>
                        </div>
                        <div class="bg-white p-3 rounded border border-green-200">
                            <div class="text-lg font-semibold text-green-700">Completed</div>
                            <div class="text-sm text-green-600">Status</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <h4 class="ml-2 text-red-800 font-medium">Processing Failed</h4>
                    </div>
                    <p class="mt-2 text-red-700">{{ $processResults['message'] }}</p>
                </div>
            @endif
        </div>
    @endif

    <div class="mt-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">What happens during payroll processing?</h3>
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="space-y-4">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-6 w-6 rounded-full bg-blue-100">
                            <span class="text-blue-600 text-sm font-medium">1</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Attendance Data Collection</h4>
                        <p class="text-sm text-gray-600">Collect attendance records for the selected period for all active employees.</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-6 w-6 rounded-full bg-blue-100">
                            <span class="text-blue-600 text-sm font-medium">2</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Basic Salary Calculation</h4>
                        <p class="text-sm text-gray-600">Calculate pro-rated basic salary based on working days and attendance.</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-6 w-6 rounded-full bg-blue-100">
                            <span class="text-blue-600 text-sm font-medium">3</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Allowances & Overtime</h4>
                        <p class="text-sm text-gray-600">Calculate fixed allowances, variable allowances, and overtime payments.</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-6 w-6 rounded-full bg-blue-100">
                            <span class="text-blue-600 text-sm font-medium">4</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Tax & Insurance Calculation</h4>
                        <p class="text-sm text-gray-600">Calculate PPh 21 tax, BPJS Kesehatan, and BPJS TK contributions.</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-6 w-6 rounded-full bg-blue-100">
                            <span class="text-blue-600 text-sm font-medium">5</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Deductions & Final Calculation</h4>
                        <p class="text-sm text-gray-600">Apply other deductions and calculate final net salary.</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-6 w-6 rounded-full bg-green-100">
                            <span class="text-green-600 text-sm font-medium">6</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Generate Payroll Records</h4>
                        <p class="text-sm text-gray-600">Create detailed payroll records and update period status.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>