<div class="space-y-6">
    <!-- Download Sample Button -->
    <div class="flex justify-end">
        <button wire:click="downloadSample"
                :disabled="$showResults"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Download Sample CSV
        </button>
    </div>

    <!-- File Upload Form -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Attendance CSV</h3>

        <form wire:submit.prevent="submit">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Attendance CSV File
                    </label>
                    <input type="file"
                           wire:model="file"
                           accept=".csv,text/csv"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    @error('file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        CSV format: employee_id, date, status, clock_in, clock_out, total_hours, overtime_hours, note
                    </p>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            :disabled="$isProcessing || !$file || $showResults"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        @if($isProcessing)
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        @else
                            Import Attendance
                        @endif
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Processing Status -->
    @if($isProcessing)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="animate-spin h-5 w-5 text-blue-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-blue-800 font-medium">Processing import...</span>
            </div>
            <p class="mt-2 text-sm text-blue-700">
                Your attendance data is being processed. Please wait...
            </p>
        </div>
    @endif

    <!-- Import Results -->
    @if($showResults && $importResults)
        <div class="bg-white border rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Import Results</h3>
                <button wire:click="clearResults" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Summary Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="text-2xl font-bold text-gray-900">{{ $importResults['total_rows'] }}</div>
                    <div class="text-sm text-gray-600">Total Rows</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                    <div class="text-2xl font-bold text-green-700">{{ $importResults['success_count'] }}</div>
                    <div class="text-sm text-green-600">Successful</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                    <div class="text-2xl font-bold text-red-700">{{ $importResults['error_count'] }}</div>
                    <div class="text-sm text-red-600">Errors</div>
                </div>
            </div>

            <!-- Error Details -->
            @if($importResults['error_count'] > 0)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-medium text-red-800">Error Details</h4>
                        <span class="text-sm text-red-600">{{ $importResults['error_count'] }} error(s)</span>
                    </div>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($importResults['results'] as $result)
                            @if($result['status'] === 'error')
                                <div class="flex items-start space-x-3 p-2 bg-white rounded border border-red-100">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 flex-shrink-0">
                                        Row {{ $result['row'] }}
                                    </span>
                                    <span class="text-sm text-red-700 break-words">{{ $result['message'] }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Success Details -->
            @if($importResults['success_count'] > 0)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-4">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-medium text-green-800">Successfully Imported</h4>
                        <span class="text-sm text-green-600">{{ $importResults['success_count'] }} record(s)</span>
                    </div>
                    <div class="space-y-2 max-h-32 overflow-y-auto">
                        @foreach($importResults['results'] as $result)
                            @if($result['status'] === 'success')
                                <div class="flex items-start space-x-3 p-2 bg-white rounded border border-green-100">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 flex-shrink-0">
                                        Row {{ $result['row'] }}
                                    </span>
                                    <span class="text-sm text-green-700 break-words">{{ $result['message'] }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex justify-end mt-6 space-x-3">
                @if($importResults['error_count'] > 0)
                    <button wire:click="clearResults" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Try Again
                    </button>
                @endif
                <button wire:click="clearResults" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    {{ $importResults['success_count'] > 0 ? 'Import More' : 'Close' }}
                </button>
            </div>
        </div>
    @endif

    <!-- CSV Format Requirements -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
        <h3 class="text-lg font-medium text-gray-900 mb-3">CSV Format Requirements</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h4 class="font-medium text-gray-800 mb-2">Required Columns:</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li><code>employee_id</code> - Employee ID number</li>
                    <li><code>date</code> - Date (YYYY-MM-DD)</li>
                    <li><code>status</code> - Attendance status</li>
                </ul>
            </div>
            <div>
                <h4 class="font-medium text-gray-800 mb-2">Optional Columns:</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li><code>clock_in</code> - Clock in time (HH:MM)</li>
                    <li><code>clock_out</code> - Clock out time (HH:MM)</li>
                    <li><code>total_hours</code> - Total working hours</li>
                    <li><code>overtime_hours</code> - Overtime hours</li>
                    <li><code>note</code> - Additional notes</li>
                </ul>
            </div>
        </div>
        <div class="mt-4">
            <h4 class="font-medium text-gray-800 mb-2">Status Values:</h4>
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Present</span>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Sick</span>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Permission</span>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Leave</span>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Absent</span>
            </div>
        </div>
    </div>
</div>
