@php
    $defaultSupervisors = value($defaultSupervisors);
@endphp

@once
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endonce


@if($defaultSupervisors->isEmpty())
    <div class="px-4 py-3 text-gray-500">Chưa có người phụ trách mặc định nào.</div>
@else
    <div class="overflow-x-auto" x-data="defaultSupervisorsTable()">
        <table class="min-w-full w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-3 bg-[#242427] text-left text-md font-medium text-gray-500 uppercase tracking-wider">Loại hợp đồng</th>
                    <th class="px-4 py-3 bg-[#242427] text-left text-md font-medium text-gray-500 uppercase tracking-wider">Vai trò</th>
                    <th class="px-4 py-3 bg-[#242427] text-left text-md font-medium text-gray-500 uppercase tracking-wider">Nhân viên</th>
                    <th class="px-4 py-3 bg-[#242427] text-left text-md font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                </tr>
            </thead>
            <tbody class="bg-[#18181b] divide-y divide-gray-200">
                @foreach($defaultSupervisors as $supervisor)
                    <tr>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $supervisor->contractType->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $supervisor->role->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $supervisor->user->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                            <button 
                                type="button" 
                                class="text-red-600 hover:text-red-900"
                                {{-- @click="confirmDelete({{ $supervisor->id }})" --}}
                                onclick="window.dispatchEvent(new CustomEvent('confirm-delete-supervisor', { detail: { id: {{ $supervisor->id }} } }))"
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
        function defaultSupervisorsTable() {
            return {
                confirmDelete(id) {
                    if (confirm('Bạn có chắc chắn muốn xóa người phụ trách mặc định này?')) {
                        Livewire.dispatch('removeDefaultSupervisor', { id: id });
                    }
                }
            }
        }
    </script>

    <script>
        window.addEventListener('confirm-delete-supervisor', event => {
            Swal.fire({
                title: 'Xác nhận xoá?',
                text: "Bạn có chắc chắn muốn xoá người phụ trách này?",
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
                    Livewire.dispatch('deleteConfirmed', { id: event.detail.id });
                }
            });
        });
    </script>

@endif

