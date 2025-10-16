<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractAnalyticsResource\Pages;
use App\Models\ContractAnalytics;
use App\Services\ContractAnalyticsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class ContractAnalyticsResource extends Resource
{
    protected static ?string $model = ContractAnalytics::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationGroup = 'Analytics';

    protected static ?string $navigationLabel = 'Contract Analytics';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('contract_id')
                    ->relationship('contract', 'contract_number')
                    ->required(),
                Forms\Components\DatePicker::make('period_date')
                    ->required(),
                Forms\Components\Select::make('period_type')
                    ->options([
                        'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'yearly' => 'Yearly',
                    ])
                    ->default('monthly')
                    ->required(),
                Forms\Components\TextInput::make('total_value')
                    ->numeric()
                    ->prefix('VND'),
                Forms\Components\TextInput::make('paid_amount')
                    ->numeric()
                    ->prefix('VND'),
                Forms\Components\TextInput::make('payment_percentage')
                    ->numeric()
                    ->suffix('%'),
                Forms\Components\Select::make('performance_status')
                    ->options([
                        'excellent' => 'Excellent',
                        'good' => 'Good', 
                        'normal' => 'Normal',
                        'poor' => 'Poor',
                        'critical' => 'Critical',
                    ]),
                Forms\Components\TextInput::make('risk_score')
                    ->numeric()
                    ->suffix('%'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contract.contract_number')
                    ->label('Contract')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('period_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('period_type')
                    ->badge()
                    ->colors([
                        'info' => 'monthly',
                        'warning' => 'quarterly',
                        'success' => 'yearly',
                    ]),
                TextColumn::make('total_value')
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('payment_percentage')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state) => match (true) {
                        $state >= 90 => 'success',
                        $state >= 70 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('performance_status')
                    ->badge()
                    ->colors([
                        'success' => 'excellent',
                        'info' => 'good',
                        'warning' => 'normal',
                        'danger' => 'poor',
                        'danger' => 'critical',
                    ]),
                TextColumn::make('risk_score')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state) => match (true) {
                        $state >= 80 => 'danger',
                        $state >= 60 => 'warning',
                        $state >= 40 => 'info',
                        default => 'success',
                    }),
                TextColumn::make('is_overdue')
                    ->label('Overdue')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')
                    ->badge()
                    ->colors([
                        'danger' => true,
                        'success' => false,
                    ]),
                TextColumn::make('completion_percentage')
                    ->suffix('%')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('period_type')
                    ->options([
                        'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'yearly' => 'Yearly',
                    ]),
                SelectFilter::make('performance_status')
                    ->options([
                        'excellent' => 'Excellent',
                        'good' => 'Good',
                        'normal' => 'Normal',
                        'poor' => 'Poor',
                        'critical' => 'Critical',
                    ]),
                Tables\Filters\Filter::make('high_risk')
                    ->query(fn (Builder $query): Builder => $query->where('risk_score', '>=', 70))
                    ->label('High Risk'),
                Tables\Filters\Filter::make('overdue')
                    ->query(fn (Builder $query): Builder => $query->where('is_overdue', true))
                    ->label('Overdue'),
            ])
            ->actions([
                Action::make('recalculate')
                    ->icon('heroicon-m-arrow-path')
                    ->action(function (ContractAnalytics $record) {
                        $service = app(ContractAnalyticsService::class);
                        $service->updateContractAnalytics($record->contract, $record->period_type);
                        
                        Notification::make()
                            ->title('Analytics Updated')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Action::make('refresh_all')
                    ->label('Refresh All Analytics')
                    ->icon('heroicon-m-arrow-path')
                    ->action(function () {
                        $service = app(ContractAnalyticsService::class);
                        $contracts = \App\Models\Contract::all();
                        
                        foreach ($contracts as $contract) {
                            $service->updateContractAnalytics($contract, 'monthly');
                        }
                        
                        Notification::make()
                            ->title('All Analytics Updated')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('period_date', 'desc');
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
            'index' => Pages\ListContractAnalytics::route('/'),
            'create' => Pages\CreateContractAnalytics::route('/create'),
            'view' => Pages\ViewContractAnalytics::route('/{record}'),
            'edit' => Pages\EditContractAnalytics::route('/{record}/edit'),
        ];
    }
}