@php
    $contractCommunications = $contractCommunications();
@endphp

<div class="fi-ta-table overflow-x-auto">
    <table class="fi-ta-table-content w-full table-auto divide-y divide-gray-200 dark:divide-white/10">
        <thead class="fi-ta-header bg-gray-50 dark:bg-white/5">
            <tr class="fi-ta-row">
                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                    <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                        STT
                    </span>
                </th>
                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                    <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                        Ngày trao đổi
                    </span>
                </th>
                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                    <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                        Người trao đổi
                    </span>
                </th>
                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                    <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                        Nội dung
                    </span>
                </th>
                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                    <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                        Tài liệu đính kèm
                    </span>
                </th>
                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                    <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                        Hành động
                    </span>
                </th>
            </tr>
        </thead>
        <tbody class="fi-ta-body divide-y divide-gray-200 dark:divide-white/10">
            @forelse($contractCommunications as $index => $communication)
                <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <div class="fi-ta-col-wrp px-3 py-4">
                            <div class="fi-ta-text text-sm leading-6 text-gray-950 dark:text-white">
                                {{ $index + 1 }}
                            </div>
                        </div>
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <div class="fi-ta-col-wrp px-3 py-4">
                            <div class="fi-ta-text text-sm leading-6 text-gray-950 dark:text-white">
                                {{ $communication->date ? \Carbon\Carbon::parse($communication->date)->format('d/m/Y') : 'N/A' }}
                            </div>
                        </div>
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <div class="fi-ta-col-wrp px-3 py-4">
                            <div class="fi-ta-text text-sm leading-6 text-gray-950 dark:text-white font-medium">
                                {{ $communication->person ?? 'Không xác định' }}
                            </div>
                        </div>
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <div class="fi-ta-col-wrp px-3 py-4">
                            <div class="fi-ta-text text-sm leading-6 text-gray-950 dark:text-white max-w-sm">
                                <div class="prose prose-sm max-w-none dark:prose-invert truncate" title="{{ strip_tags($communication->content) }}">
                                    {!! Str::limit(strip_tags($communication->content), 100) !!}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <div class="fi-ta-col-wrp px-3 py-4">
                            @if(!empty($communication->attachments) && is_array($communication->attachments) && count($communication->attachments) > 0)
                                <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-h-6 bg-primary-50 text-primary-600 ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30">
                                    {{ count($communication->attachments) }} file(s)
                                </span>
                            @else
                                <span class="fi-ta-text text-xs text-gray-500 dark:text-gray-400">Không có</span>
                            @endif
                        </div>
                    </td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <div class="fi-ta-col-wrp px-3 py-4">
                            <div class="fi-ta-actions flex shrink-0 items-center gap-3">
                                <button type="button"
                                        onclick="alert('{{ addslashes(strip_tags($communication->content)) }}')"
                                        class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-sm fi-btn-size-sm gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action">
                                    <svg class="fi-btn-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <span class="fi-btn-label">Xem</span>
                                </button>
                                
                                <button type="button"
                                        onclick="if(confirm('Bạn có chắc chắn muốn xóa trao đổi này?')) { $wire.call('deleteContractCommunicationConfirmed', {{ $communication->id ?? 0 }}) }"
                                        class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-danger fi-btn-color-danger fi-size-sm fi-btn-size-sm gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-danger-600 text-white hover:bg-danger-500 focus-visible:ring-danger-500/50 dark:bg-danger-500 dark:hover:bg-danger-400 dark:focus-visible:ring-danger-400/50 fi-ac-action fi-ac-btn-action">
                                    <svg class="fi-btn-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    <span class="fi-btn-label">Xóa</span>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="fi-ta-row">
                    <td colspan="6" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <div class="fi-ta-empty-state px-6 py-12">
                            <div class="fi-ta-empty-state-content mx-auto grid max-w-lg justify-items-center text-center">
                                <div class="fi-ta-empty-state-icon-ctn mb-4 rounded-full bg-gray-100 p-3 dark:bg-gray-500/20">
                                    <svg class="fi-ta-empty-state-icon h-6 w-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <h4 class="fi-ta-empty-state-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                    Chưa có trao đổi
                                </h4>
                                <p class="fi-ta-empty-state-description text-sm text-gray-500 dark:text-gray-400">
                                    Nhấn "Thêm" để thêm lịch sử trao đổi cho hợp đồng này
                                </p>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($contractCommunications->count() > 0)
    <div class="fi-ta-footer px-3 py-4 sm:px-6">
        <div class="fi-ta-pagination-summary text-sm leading-6 text-gray-600 dark:text-gray-400">
            Tổng số trao đổi: <span class="font-medium">{{ $contractCommunications->count() }}</span>
        </div>
    </div>
@endif