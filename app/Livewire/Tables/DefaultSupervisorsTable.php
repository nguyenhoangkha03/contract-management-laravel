<?php

/**
 * @method \Filament\Support\Contracts\TranslatableContentDriver|null makeFilamentTranslatableContentDriver()
 */


namespace App\Http\Livewire\Tables;

use App\Models\Supervisor;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Component;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;

class DefaultSupervisorsTable extends Component implements HasTable
{
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Supervisor::query()->with(['contractType', 'role', 'user']))
            ->columns([
                Tables\Columns\TextColumn::make('contractType.name')->label('Loại hợp đồng'),
                Tables\Columns\TextColumn::make('role.name')->label('Vai trò'),
                Tables\Columns\TextColumn::make('user.name')->label('Người phụ trách'),
                Tables\Columns\IconColumn::make('actions')
                    ->label('Thao tác')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->action(fn(Supervisor $record) => $this->delete($record))
            ]);
    }

    public function delete(Supervisor $record): void
    {
        $record->delete();
        $this->dispatch('notify', 'Xóa thành công!');
    }


    public function render()
    {
        return view('livewire.tables.default-supervisors-table');
    }

    public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
    {
        return null;
    }
}
