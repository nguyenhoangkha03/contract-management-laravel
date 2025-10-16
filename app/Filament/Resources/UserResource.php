<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $pluralLabel = 'Nhân Viên';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Họ Tên'),
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->email()
                    ->label('Email'),
                Forms\Components\TextInput::make('password')
                    ->required()
                    ->email()
                    ->label('Cấp Lại Mật Khẩu')
                    ->dehydrated(fn($state) => filled($state))
                    ->visibleOn('edit'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->label('Mật Khẩu')
                    ->dehydrated(fn($state) => filled($state))
                    ->visibleOn('create'),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->label('Số Điện Thoại'),
                Forms\Components\TextInput::make('address')
                    ->label('Địa Chỉ'),
                Forms\Components\DatePicker::make('birth')
                    ->label('Ngày Sinh')
                    ->date()
                    ->placeholder('YYYY-MM-DD')
                    ->maxDate(now()),
                Forms\Components\FileUpload::make('avatar')
                    ->label('Hình Ảnh')
                    ->image()
                    ->directory('avatars')
                    ->imagePreviewHeight('200')
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable(),
                Forms\Components\Select::make('department_id')
                    ->relationship('department', 'name')
                    ->required()
                    ->label('Phòng Ban'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Họ Tên')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Số Điện Thoại')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Địa Chỉ')
                    ->limit(50)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('birth')
                    ->label('Ngày Sinh')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Hình Ảnh')
                    ->size(100)
                    ->circular(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Phòng Ban')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Xem'),
                Tables\Actions\EditAction::make()->label('Chỉnh sửa'),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Xác nhận xóa người dùng')
                    ->modalDescription('Bạn có chắc chắn muốn xóa người dùng này?')
                    ->modalButton('Xóa ngay')
                    ->label('Xóa'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Hệ Thống';
    }
}
