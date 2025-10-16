<x-filament::page>
    <div class="grid grid-cols-12 gap-6">
        {{-- Cột trái: Thông tin hợp đồng --}}
        <div class="col-span-4">
            <x-filament::card>
                <h2 class="text-lg font-bold mb-4">Cửa hàng {{ $record->store_name }}</h2>
                <dl class="space-y-2">
                    <div><strong>Số hợp đồng:</strong> {{ $record->code }}</div>
                    <div><strong>Loại hợp đồng:</strong> {{ $record->contract_type }}</div>
                    <div><strong>Ngày ký:</strong> {{ $record->sign_date->format('d/m/Y') }}</div>
                    <div><strong>Ngày hiệu lực:</strong> {{ $record->start_date->format('d/m/Y') }}</div>
                    <div><strong>Ngày hết hiệu lực:</strong> {{ $record->end_date->format('d/m/Y') }}</div>
                    <div><strong>Giá trị hợp đồng:</strong> {{ number_format($record->total_value, 0, ',', '.') }}</div>
                    <div><strong>Tổng giá trị đã thanh toán:</strong> {{ number_format($record->paid_value, 0, ',', '.') }}</div>
                    <div><strong>Giá trị chưa thanh toán:</strong> {{ number_format($record->total_value - $record->paid_value, 0, ',', '.') }}</div>
                    <div><strong>Người xử lý:</strong> {{ $record->handler }}</div>
                    <div><strong>Ngày xử lý:</strong> {{ $record->handled_at?->format('d/m/Y') }}</div>
                </dl>
                <div class="mt-4">
                    <x-filament::button>Tải hợp đồng</x-filament::button>
                </div>
            </x-filament::card>
        </div>

        {{-- Cột phải: Tabs nội dung --}}
        <div class="col-span-8">
            <x-filament::tabs>
                <x-filament::tabs.item label="Thông tin">
                    @include('contracts.partials.info-tab', ['record' => $record])
                </x-filament::tabs.item>

                <x-filament::tabs.item label="Ghi chú">
                    @include('contracts.partials.notes-tab', ['record' => $record])
                </x-filament::tabs.item>

                <x-filament::tabs.item label="Hóa đơn">
                    @include('contracts.partials.invoices-tab', ['record' => $record])
                </x-filament::tabs.item>

                <x-filament::tabs.item label="Đính kèm">
                    @include('contracts.partials.attachments-tab', ['record' => $record])
                </x-filament::tabs.item>
            </x-filament::tabs>
        </div>
    </div>
</x-filament::page>
