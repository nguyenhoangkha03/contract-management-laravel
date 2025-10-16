<div>
    <x-filament::table>
        <x-slot name="header">
            <x-filament::table.heading>
                Họ tên
            </x-filament::table.heading>
            <x-filament::table.heading>
                Vai trò
            </x-filament::table.heading>
            <x-filament::table.heading>
                Ghi chú
            </x-filament::table.heading>
            <x-filament::table.heading>
                Thao tác
            </x-filament::table.heading>
        </x-slot>
        
        @foreach($getSupervisors() as $supervisor)
            <x-filament::table.row>
                <x-filament::table.cell>
                    {{ $supervisor->user->name }}
                </x-filament::table.cell>
                <x-filament::table.cell>
                    {{ $supervisor->role }}
                </x-filament::table.cell>
                <x-filament::table.cell>
                    {{ $supervisor->note }}
                </x-filament::table.cell>
                <x-filament::table.cell>
                    <x-filament::button
                        wire:click="removeSupervisor({{ $supervisor->id }})"
                        color="danger"
                        size="sm"
                    >
                        Xóa
                    </x-filament::button>
                </x-filament::table.cell>
            </x-filament::table.row>
        @endforeach
    </x-filament::table>
</div>