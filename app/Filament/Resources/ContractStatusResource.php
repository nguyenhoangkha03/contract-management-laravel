<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractStatusResource\Pages;
use App\Filament\Resources\ContractStatusResource\RelationManagers;
use App\Models\ContractStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractStatusResource extends Resource
{
    protected static ?string $model = ContractStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255)
                    ->label('Mã Trạng Thái'),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Tên Trạng Thái'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stt')
                    ->label('STT')
                    ->state(fn($livewire) => $livewire->getTableRecordsPerPage() * ($livewire->getTablePage() - 1))
                    ->formatStateUsing(fn($state, $record, $livewire) => $state + $livewire->getTableRecords()->search($record) + 1)
                    ->sortable()
                    ->label('STT'),
                Tables\Columns\TextColumn::make('code')
                    ->sortable()
                    ->searchable()
                    ->label('Mã Trạng Thái'),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Tên Trạng Thái'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Chi tiết'),
                    Tables\Actions\EditAction::make()->label('Chỉnh sửa'),
                    Tables\Actions\DeleteAction::make()->label('Xóa'),
                ]),
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
            'index' => Pages\ListContractStatuses::route('/'),
            'create' => Pages\CreateContractStatus::route('/create'),
            'edit' => Pages\EditContractStatus::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Thông Tin Hợp Đồng';
    }

    public static function getNavigationLabel(): string
    {
        return 'Trạng Thái';
    }
}
