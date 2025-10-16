@php
    $exportTemplates = value($exportTemplates);
@endphp

@once
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endonce

@if($exportTemplates->isEmpty())
    <div class="px-4 py-3 text-gray-500">Chưa có mẫu xuất nào.</div>
@else
    <div class="overflow-x-auto" x-data="exportTemplatesTable()">
        <table class="min-w-full w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-3 bg-[#242427] text-left text-md font-medium text-gray-500 uppercase tracking-wider">Tên mẫu</th>
                    <th class="px-4 py-3 bg-[#242427] text-left text-md font-medium text-gray-500 uppercase tracking-wider">File mẫu</th>
                    <th class="px-4 py-3 bg-[#242427] text-left text-md font-medium text-gray-500 uppercase tracking-wider">Loại hợp đồng áp dụng</th>
                    <th class="px-4 py-3 bg-[#242427] text-left text-md font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                </tr>
            </thead>
            <tbody class="bg-[#18181b] divide-y divide-gray-200">
                @foreach($exportTemplates as $template)
                    <tr>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $template->name }}</td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                            <a href="{{ Storage::url($template->file_path) }}" target="_blank" class="text-blue-500 hover:underline">
                                {{ basename($template->file_path) }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $template->contractType->name ?? 'Tất cả' }}</td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                            <button 
                                type="button" 
                                class="text-red-600 hover:text-red-900"
                                onclick="window.dispatchEvent(new CustomEvent('confirm-delete-template', { detail: { id: {{ $template->id }} } }))"
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

    <script>
        function exportTemplatesTable() {
            return {
                confirmDelete(id) {
                    if (confirm('Bạn có chắc chắn muốn xóa mẫu xuất này?')) {
                        Livewire.dispatch('removeExportTemplate', { id: id });
                    }
                }
            }
        }
    </script>

    <script>
        window.addEventListener('confirm-delete-template', event => {
            Swal.fire({
                title: 'Xác nhận xoá?',
                text: "Bạn có chắc chắn muốn xoá mẫu xuất này?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Xoá',
                cancelButtonText: 'Huỷ',
                background: '#1f2937',  // Màu nền dark mode
                color: '#fff',          // Text màu trắng
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('deleteTemplateConfirmed', { id: event.detail.id });
                }
            });
        });
    </script>
@endif