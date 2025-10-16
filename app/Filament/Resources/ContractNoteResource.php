<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractNoteResource\Pages;
use App\Filament\Resources\ContractNoteResource\RelationManagers;
use App\Models\ContractNote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractNoteResource extends Resource
{
    protected static ?string $model = ContractNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListContractNotes::route('/'),
            'create' => Pages\CreateContractNote::route('/create'),
            'edit' => Pages\EditContractNote::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Thông Tin Hợp Đồng';
    }

    public static function getNavigationLabel(): string
    {
        return 'Ghi Chú';
    }
}
