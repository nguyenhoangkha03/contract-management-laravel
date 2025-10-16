<x-filament::page>
    <x-filament::tabs>
        <x-filament::tabs.tab name="Thông tin">
            <!-- Hiển thị thông tin hợp đồng chung -->
            <div>
                <p><strong>Mã Hợp Đồng:</strong> {{ $record->contract_code }}</p>
                <p><strong>Loại Hợp Đồng:</strong> {{ $record->contractType->type_name }}</p>
                <p><strong>Ngày hiệu lực:</strong> {{ $record->effective_date->format('d/m/Y') }}</p>
                <p><strong>Ngày hết hạn:</strong> {{ $record->expiration_date->format('d/m/Y') }}</p>
                <p><strong>Giá trị:</strong> {{ number_format($record->value, 0) }}</p>
                <!-- Các thông tin khác ... -->
            </div>
        </x-filament::tabs.tab>

        <x-filament::tabs.tab name="Hàng hóa">
            <!-- Có thể tích hợp Relation Manager dùng để quản lý contract items -->
            @livewire('filament.resources.contract-resource.contract-items', ['record' => $record])
        </x-filament::tabs.tab>

        <x-filament::tabs.tab name="Trao đổi">
            <!-- Hiển thị trao đổi, chat hoặc lịch sử trao đổi liên quan -->
            @livewire('filament.resources.contract-resource.exchange', ['record' => $record])
        </x-filament::tabs.tab>

        <x-filament::tabs.tab name="Ghi chú">
            <!-- Bạn có thể dùng Relation Manager hoặc Livewire component để quản lý ghi chú -->
            @livewire('filament.resources.contract-resource.contract-notes', ['record' => $record])
        </x-filament::tabs.tab>

        <x-filament::tabs.tab name="Thanh toán">
            <!-- Quản lý thông tin thanh toán -->
            @livewire('filament.resources.contract-resource.payments', ['record' => $record])
        </x-filament::tabs.tab>

        <x-filament::tabs.tab name="Hóa đơn">
            <!-- Quản lý hóa đơn liên quan -->
            @livewire('filament.resources.contract-resource.invoices', ['record' => $record])
        </x-filament::tabs.tab>

        <x-filament::tabs.tab name="Đính kèm">
            <!-- Quản lý tệp đính kèm -->
            @livewire('filament.resources.contract-resource.contract-attachments', ['record' => $record])
        </x-filament::tabs.tab>

        <x-filament::tabs.tab name="Hoạt động">
            <!-- Lịch sử hoạt động của hợp đồng -->
            @livewire('filament.resources.contract-resource.contract-activities', ['record' => $record])
        </x-filament::tabs.tab>

        <x-filament::tabs.tab name="Công việc">
            <!-- Quản lý công việc, nhiệm vụ liên quan đến hợp đồng -->
            @livewire('filament.resources.contract-resource.contract-tasks', ['record' => $record])
        </x-filament::tabs.tab>
    </x-filament::tabs>
</x-filament::page>
