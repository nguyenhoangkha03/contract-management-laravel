<?php

/**
 * @method \Filament\Support\Contracts\TranslatableContentDriver|null makeFilamentTranslatableContentDriver()
 */

use App\Models\Supervisor;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Livewire\Component;

class SupervisorsTable extends Component implements HasTable
{
    use InteractsWithTable;

    protected function getTableQuery()
    {
        return Supervisor::query()->with(['contractType', 'role', 'user']);
    }

    protected function getTableColumns(): array
    {
        return [
            \Filament\Tables\Columns\TextColumn::make('contractType.name')->label('Loại hợp đồng'),
            \Filament\Tables\Columns\TextColumn::make('role.name')->label('Vai trò'),
            \Filament\Tables\Columns\TextColumn::make('user.name')->label('Người phụ trách'),
        ];
    }

    public function render()
    {
        return view('livewire.tables.supervisors-table');
    }
}
