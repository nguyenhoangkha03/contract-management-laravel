<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractAdjustmentResource\Pages;
use App\Filament\Resources\ContractAdjustmentResource\RelationManagers;
use App\Models\ContractAdjustment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractAdjustmentResource extends Resource
{
    protected static ?string $model = ContractAdjustment::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?int $navigationSort = 4;

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
            'index' => Pages\ListContractAdjustments::route('/'),
            'create' => Pages\CreateContractAdjustment::route('/create'),
            'edit' => Pages\EditContractAdjustment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Thông Tin Hợp Đồng';
    }

    public static function getNavigationLabel(): string
    {
        return 'Điều Chỉnh';
    }
}
