{{-- <div class="flex justify-end">
    <x-filament::button wire:click="$set('data.show_add_export_template', true)">
        Thêm mẫu mới
    </x-filament::button>
</div> --}}

<div class="flex justify-end">
    <button
        type="button"
        x-data
        @click="$wire.set('data.show_add_export_template', !$wire.entangle('data.show_add_export_template').defer)"
        class="filament-button inline-flex items-center justify-center rounded-lg border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 4v16m8-8H4"/>
        </svg>
        &nbsp;&nbsp;
        <span
            x-text="$wire.entangle('data.show_add_export_template').defer ? 'Ẩn form thêm mẫu' : 'Thêm mới'"
        ></span>
    </button>
</div>




