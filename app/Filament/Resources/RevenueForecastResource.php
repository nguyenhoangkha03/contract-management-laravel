<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RevenueForecastResource\Pages;
use App\Models\RevenueForecast;
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

class RevenueForecastResource extends Resource
{
    protected static ?string $model = RevenueForecast::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    
    protected static ?string $navigationGroup = 'Analytics';

    protected static ?string $navigationLabel = 'Revenue Forecast';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('forecast_date')
                    ->required(),
                Forms\Components\Select::make('forecast_type')
                    ->options([
                        'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'yearly' => 'Yearly',
                    ])
                    ->default('monthly')
                    ->required(),
                Forms\Components\Select::make('forecast_method')
                    ->options([
                        'linear' => 'Linear Regression',
                        'exponential' => 'Exponential Smoothing',
                        'seasonal' => 'Seasonal Decomposition',
                    ])
                    ->default('linear')
                    ->required(),
                Forms\Components\TextInput::make('predicted_revenue')
                    ->numeric()
                    ->prefix('VND')
                    ->required(),
                Forms\Components\TextInput::make('actual_revenue')
                    ->numeric()
                    ->prefix('VND'),
                Forms\Components\TextInput::make('confidence_level')
                    ->numeric()
                    ->suffix('%')
                    ->minValue(0)
                    ->maxValue(100),
                Forms\Components\TextInput::make('growth_rate')
                    ->numeric()
                    ->suffix('%'),
                Forms\Components\Select::make('trend_direction')
                    ->options([
                        'increasing' => 'Increasing',
                        'decreasing' => 'Decreasing',
                        'stable' => 'Stable',
                    ])
                    ->default('stable'),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('forecast_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('forecast_type')
                    ->badge()
                    ->colors([
                        'info' => 'monthly',
                        'warning' => 'quarterly',
                        'success' => 'yearly',
                    ]),
                TextColumn::make('forecast_method')
                    ->badge()
                    ->colors([
                        'primary' => 'linear',
                        'secondary' => 'exponential',
                        'success' => 'seasonal',
                    ]),
                TextColumn::make('predicted_revenue')
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('actual_revenue')
                    ->money('VND')
                    ->sortable()
                    ->placeholder('N/A'),
                TextColumn::make('accuracy_percentage')
                    ->label('Accuracy')
                    ->suffix('%')
                    ->placeholder('N/A')
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        $state >= 90 => 'success',
                        $state >= 80 => 'info',
                        $state >= 70 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('confidence_level')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state) => match (true) {
                        $state >= 90 => 'success',
                        $state >= 80 => 'info',
                        $state >= 70 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('growth_rate')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('trend_direction')
                    ->badge()
                    ->colors([
                        'success' => 'increasing',
                        'danger' => 'decreasing',
                        'warning' => 'stable',
                    ]),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('forecast_type')
                    ->options([
                        'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'yearly' => 'Yearly',
                    ]),
                SelectFilter::make('forecast_method')
                    ->options([
                        'linear' => 'Linear Regression',
                        'exponential' => 'Exponential Smoothing',
                        'seasonal' => 'Seasonal Decomposition',
                    ]),
                SelectFilter::make('trend_direction')
                    ->options([
                        'increasing' => 'Increasing',
                        'decreasing' => 'Decreasing',
                        'stable' => 'Stable',
                    ]),
                Tables\Filters\Filter::make('high_confidence')
                    ->query(fn (Builder $query): Builder => $query->where('confidence_level', '>=', 80))
                    ->label('High Confidence (≥80%)'),
                Tables\Filters\Filter::make('with_actuals')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('actual_revenue'))
                    ->label('With Actual Data'),
            ])
            ->actions([
                Action::make('update_actual')
                    ->label('Update Actual')
                    ->icon('heroicon-m-pencil-square')
                    ->form([
                        Forms\Components\TextInput::make('actual_revenue')
                            ->numeric()
                            ->prefix('VND')
                            ->required(),
                    ])
                    ->action(function (RevenueForecast $record, array $data) {
                        $record->update([
                            'actual_revenue' => $data['actual_revenue'],
                            'accuracy_score' => $record->getAccuracyPercentageAttribute(),
                        ]);
                        
                        Notification::make()
                            ->title('Actual Revenue Updated')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (RevenueForecast $record) => $record->actual_revenue === null),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Action::make('generate_forecast')
                    ->label('Generate New Forecast')
                    ->icon('heroicon-m-sparkles')
                    ->form([
                        Forms\Components\Select::make('forecast_type')
                            ->options([
                                'monthly' => 'Monthly',
                                'quarterly' => 'Quarterly',
                                'yearly' => 'Yearly',
                            ])
                            ->default('monthly')
                            ->required(),
                        Forms\Components\TextInput::make('periods_ahead')
                            ->numeric()
                            ->default(12)
                            ->minValue(1)
                            ->maxValue(24)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $service = app(ContractAnalyticsService::class);
                        $forecasts = $service->generateRevenueForecast($data['forecast_type'], $data['periods_ahead']);
                        
                        foreach ($forecasts as $forecast) {
                            $service->saveRevenueForecast($forecast);
                        }
                        
                        Notification::make()
                            ->title('Revenue Forecast Generated')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('forecast_date', 'desc');
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
            'index' => Pages\ListRevenueForecasts::route('/'),
            'create' => Pages\CreateRevenueForecast::route('/create'),
            'view' => Pages\ViewRevenueForecast::route('/{record}'),
            'edit' => Pages\EditRevenueForecast::route('/{record}/edit'),
        ];
    }
}