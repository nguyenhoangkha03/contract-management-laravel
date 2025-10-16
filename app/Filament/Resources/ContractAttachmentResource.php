<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractAttachmentResource\Pages;
use App\Filament\Resources\ContractAttachmentResource\RelationManagers;
use App\Models\ContractAttachment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractAttachmentResource extends Resource
{
    protected static ?string $model = ContractAttachment::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';

    protected static ?int $navigationSort = 2;

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
            'index' => Pages\ListContractAttachments::route('/'),
            'create' => Pages\CreateContractAttachment::route('/create'),
            'edit' => Pages\EditContractAttachment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Thông Tin Hợp Đồng';
    }

    public static function getNavigationLabel(): string
    {
        return 'Tệp Đính Kèm';
    }
}
