<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractTypeResource\Pages;
use App\Filament\Resources\ContractTypeResource\RelationManagers;
use App\Models\ContractType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractTypeResource extends Resource
{
    protected static ?string $model = ContractType::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?int $navigationSort = 1;

    protected static ?string $pluralLabel = 'Loại Hợp Đồng';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('Tên Loại'),
                Forms\Components\Textarea::make('description')
                    ->label('Mô Tả'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stt')
                    ->label('STT')
                    ->state(fn($livewire) => $livewire->getTableRecordsPerPage() * ($livewire->getTablePage() - 1))
                    ->formatStateUsing(fn($state, $record, $livewire) => $state + $livewire->getTableRecords()->search($record) + 1),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable()->label('Tên Loại'),
                Tables\Columns\TextColumn::make('description')->limit(50)->label('Mô Tả'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Xem'),
                Tables\Actions\EditAction::make()->label('Chỉnh Sửa'),
                Tables\Actions\DeleteAction::make()->label('Xóa')
                    ->requiresConfirmation()
                    ->modalHeading('Xóa Loại Hợp Đồng')
                    ->modalSubheading('Bạn có chắc chắn muốn xóa loại hợp đồng này không?')
                    ->modalButton('Xóa')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContractTypes::route('/'),
            'create' => Pages\CreateContractType::route('/create'),
            'edit' => Pages\EditContractType::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Hợp Đồng';
    }

    public static function getNavigationLabel(): string
    {
        return 'Loại Hợp Đồng';
    }
}
