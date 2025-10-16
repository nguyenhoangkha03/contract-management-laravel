@php
    $contractProducts = value($contractProducts);
    $count = 0;
@endphp

@once
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endonce

@if($contractProducts->isEmpty())
    <div class="px-4 py-3 text-gray-500">Chưa có hàng hóa nào.</div>
@else
    <div class="overflow-x-auto">
        <table class="min-w-full w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-3 bg-[#111827] text-left text-md font-medium text-gray-500 tracking-wider">STT</th>
                    <th class="px-4 py-3 bg-[#111827] text-left text-md font-medium text-gray-500 tracking-wider">Tên sản phẩm</th>
                    <th class="px-4 py-3 bg-[#111827] text-left text-md font-medium text-gray-500 tracking-wider">Đơn vị tính</th>
                    <th class="px-4 py-3 bg-[#111827] text-left text-md font-medium text-gray-500 tracking-wider">Số lượng</th>
                    <th class="px-4 py-3 bg-[#111827] text-left text-md font-medium text-gray-500 tracking-wider">Đơn giá</th>
                    <th class="px-4 py-3 bg-[#111827] text-left text-md font-medium text-gray-500 tracking-wider">Thành tiền</th>
                    <th class="px-4 py-3 bg-[#111827] text-left text-md font-medium text-gray-500 tracking-wider">Thao tác</th>
                </tr>
            </thead>
            <tbody class="bg-[#111827] divide-y divide-gray-200">
                @foreach($contractProducts as $product)
                    <tr>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ ++$count }}</td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $product->product->name }}</td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                            {{ $product->product->unit }}
                        </td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $product->number}}</td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ number_format($product->product->price, 0, ',', '.') }} đ</td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ number_format($product->total, 0, ',', '.') }} đ</td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                            <button 
                                type="button" 
                                class="text-red-600 hover:text-red-900"
                                onclick="window.dispatchEvent(new CustomEvent('confirm-delete-product', { detail: { id: {{ $product->id }} } }))"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- <script>
        function contractProductsTable() {
            return {
                confirmDelete(id) {
                    if (confirm('Bạn có chắc chắn muốn xóa mẫu xuất này?')) {
                        Livewire.dispatch('removeExportTemplate', { id: id });
                    }
                }
            }
        }
    </script> --}}

    <script>
        window.addEventListener('confirm-delete-product', event => {
            Swal.fire({
                title: 'Xác nhận xoá?',
                text: "Bạn có chắc chắn muốn xoá hàng hóa này?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Xoá',
                cancelButtonText: 'Huỷ',
                background: '#1f2937', 
                color: '#fff',          
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('deleteContractProductConfirmed', { id: event.detail.id });
                }
            });
        });
    </script>
@endif