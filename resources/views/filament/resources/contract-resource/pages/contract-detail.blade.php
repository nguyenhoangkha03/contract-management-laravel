<x-filament-panels::page>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <div class="space-y-6 bg-gray-900 rounded-xl">
        <div class="p-4 rounded-lg shadow">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">
                    {{ $this->ContractTypeName() }}
                </h2>
                <div class="flex gap-2">
                    <x-filament::button color="warning" wire:click="downloadWord">
                        Sao chép
                    </x-filament::button>
                    <x-filament::button color="danger">
                        Xóa hợp đồng
                    </x-filament::button>
                    <x-filament::button color="gray">
                        Thiết lập
                    </x-filament::button>
                </div>
            </div>
            <div>
                {{ $this->contractInfolist }}
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6 p-4">
            <div class="col-span-3 bg-gray-900 rounded-lg shadow">
                <div class="p-4 space-y-4 border border-gray-800 rounded-xl">
                    <h3 class="font-bold text-lg text-center">{{ $this->ClientName() }}</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Số hợp đồng</span>
                            <span class="text-right">{{ $record->contract_number ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-400">Loại hợp đồng</span>
                            <span class="text-right">{{ $record->contractType->name ?? 'NA' }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-400">Ngày ký</span>
                            <span class="text-right">{{ $record->sign_date ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-400">Ngày hiệu lực</span>
                            <span class="text-right">{{ $record->start_date ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-400">Ngày hết hiệu lực</span>
                            <span class="text-right">{{ $record->end_date ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-400">Giá trị hợp đồng</span>
                            <span class="text-right">{{ $record->total_value ?? '0' }} VNĐ</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-400">Tổng giá trị đã thanh toán</span>
                            <span class="text-right">{{ $record->total_value ?? '0' }} VNĐ</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-400">Giá trị chưa thanh toán</span>
                            <span class="text-right">{{ $record->total_value ?? '0' }} VNĐ</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-filament::button class="w-full" onclick="openModal()">
                            <x-slot name="icon">
                                <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                            </x-slot>
                            Tải hợp đồng
                        </x-filament::button>
                    </div>
                </div>

                {{-- <div class="mt-4 px-4 py-2 flex justify-center items-center">
                    {{ $this->ButtonDownload() }}
                </div> --}}
                
                <div class="mt-4 px-4 pb-4">
                    {{ $this->SupervisorTab }}
                </div>
            </div>
            
            <div class="col-span-9 bg-gray-900 rounded-lg shadow">
                <div class="p-4">
                    {{ $this->form }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            function openModal() {
                Swal.fire({
                    title: 'Chọn mẫu hợp đồng',
                    html: `
                        <div class="space-y-4">
                            <label for="contractTemplate">Chọn mẫu hợp đồng:</label>
                            <select id="contractTemplate" class="w-full p-2 border border-gray-600 rounded          text-gray-900">
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}">{{ ucfirst($template->name) }}</option>
                                @endforeach
                            </select>
                        </div>`,
                    showCancelButton: true,
                    confirmButtonText: 'Chọn',
                    cancelButtonText: 'Hủy',
                    background: '#1f2937',
                    color: '#fff',
                }).then((result) => {
                    if (result.isConfirmed) {
                        const selectedTemplate = document.getElementById('contractTemplate').value;
                        Livewire.dispatch('downloadWord', { id: selectedTemplate });
                    }
                });
            }
        </script>
    @endpush
</x-filament-panels::page>