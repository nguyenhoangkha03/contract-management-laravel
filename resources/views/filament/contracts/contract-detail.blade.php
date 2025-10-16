<x-filament::page>
    <!-- HEADER -->
    <div class="flex justify-between items-center p-4 border-b">
        <!-- Thanh trạng thái (clickable dropdown) -->
        <div>
            <x-filament::dropdown align="left" width="48">
                <x-slot name="trigger">
                    <button class="btn btn-sm">
                        {{ $record->contractStatus->status_name }}
                    </button>
                </x-slot>
                <x-slot name="content">
                    @foreach($statuses as $status)
                        <!-- Khi click, gọi action (có thể dùng Livewire) -->
                        <x-filament::dropdown.link wire:click="updateStatus({{ $status->status_id }})">
                            {{ $status->status_name }}
                        </x-filament::dropdown.link>
                    @endforeach
                </x-slot>
            </x-filament::dropdown>
        </div>
        <!-- Nút lưu & xóa hợp đồng -->
        <div class="space-x-2">
            <button type="button" class="btn btn-primary" wire:click="saveContract">
                Lưu hợp đồng
            </button>
            <button type="button" class="btn btn-danger" wire:click="deleteContract">
                Xóa hợp đồng
            </button>
        </div>
    </div>

    <!-- NỘI DUNG CHÍNH -->
    <div class="flex">
        <!-- Cột bên trái: Thông tin chung -->
        <div class="w-1/3 p-4 border-r">
            <h2 class="text-lg font-bold mb-2">Thông tin hợp đồng</h2>
            <div class="space-y-2">
                <p><strong>Mã hợp đồng:</strong> {{ $record->contract_code }}</p>
                <p><strong>Loại hợp đồng:</strong> {{ $record->contractType->type_name }}</p>
                <p>
                    <strong>Ngày hiệu lực:</strong>
                    {{ \Carbon\Carbon::make($record->effective_date)->format('d/m/Y') }}
                </p>
                <p>
                    <strong>Ngày hết hạn:</strong>
                    {{ \Carbon\Carbon::make($record->expiration_date)->format('d/m/Y') }}
                </p>
                <p><strong>Giá trị:</strong> {{ number_format($record->value, 0) }}</p>
                <!-- Các thông tin khác có thể thêm vào đây -->
            </div>
        </div>

        <!-- Cột bên phải: Menu và form cập nhật theo mục -->
        <div class="w-2/3 p-4">
            <!-- Menu trên cùng -->
            <div class="border-b mb-4">
                <ul class="flex space-x-4">
                    <li class="cursor-pointer px-3 py-2
                        {{ $activeMenu === 'general' ? 'text-primary border-b-2 border-primary' : 'text-gray-500' }}"
                        wire:click="$set('activeMenu', 'general')">
                        Thông tin
                    </li>
                    <li class="cursor-pointer px-3 py-2
                        {{ $activeMenu === 'items' ? 'text-primary border-b-2 border-primary' : 'text-gray-500' }}"
                        wire:click="$set('activeMenu', 'items')">
                        Hàng hóa
                    </li>
                    <li class="cursor-pointer px-3 py-2
                        {{ $activeMenu === 'exchange' ? 'text-primary border-b-2 border-primary' : 'text-gray-500' }}"
                        wire:click="$set('activeMenu', 'exchange')">
                        Trao đổi
                    </li>
                    <li class="cursor-pointer px-3 py-2
                        {{ $activeMenu === 'notes' ? 'text-primary border-b-2 border-primary' : 'text-gray-500' }}"
                        wire:click="$set('activeMenu', 'notes')">
                        Ghi chú
                    </li>
                    <li class="cursor-pointer px-3 py-2
                        {{ $activeMenu === 'payment' ? 'text-primary border-b-2 border-primary' : 'text-gray-500' }}"
                        wire:click="$set('activeMenu', 'payment')">
                        Thanh toán
                    </li>
                    <!-- Thêm các mục khác nếu cần -->
                </ul>
            </div>

            <!-- Phần form cập nhật chi tiết, thay đổi theo menu được chọn -->
            <div>
                @if($activeMenu === 'general')
                    @livewire('contract-general-form', ['contract' => $record])
                @elseif($activeMenu === 'items')
                    @livewire('contract-items-form', ['contract' => $record])
                @elseif($activeMenu === 'exchange')
                    @livewire('contract-exchange-form', ['contract' => $record])
                @elseif($activeMenu === 'notes')
                    @livewire('contract-notes-form', ['contract' => $record])
                @elseif($activeMenu === 'payment')
                    @livewire('contract-payment-form', ['contract' => $record])
                @endif
            </div>
        </div>
    </div>
</x-filament::page>
