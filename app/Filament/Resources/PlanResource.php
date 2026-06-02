<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Models\Plan;
use App\Services\CostCalculator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Plan Details')
                    ->schema([
                        Forms\Components\Select::make('gpu_tier')
                            ->options(
                                collect(config('runpod.gpu_tiers'))
                                    ->mapWithKeys(fn ($gpu, $key) => [$key => $gpu['display']])
                                    ->toArray()
                            )
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('Pricing Overrides')
                    ->schema([
                        Forms\Components\TextInput::make('benefit_margin_rate')
                            ->label('Benefit Margin (%)')
                            ->numeric()
                            ->suffix('%')
                            ->helperText('Default: 35%')
                            ->formatStateUsing(fn ($state) => $state ? $state * 100 : 35)
                            ->dehydrateStateUsing(fn ($state) => ($state ?: 35) / 100),
                        Forms\Components\TextInput::make('fixed_markup')
                            ->label('Fixed Platform Markup ($)')
                            ->numeric()
                            ->prefix('$')
                            ->helperText('Default: $9.99'),
                        Forms\Components\TextInput::make('storage_cost_monthly')
                            ->label('Storage Cost ($/mo)')
                            ->numeric()
                            ->prefix('$'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gpu_tier')
                    ->badge()
                    ->formatStateUsing(fn ($state) => config("runpod.gpu_tiers.{$state}.display", $state)),
                Tables\Columns\TextColumn::make('base_hourly_rate')
                    ->money('usd')
                    ->label('Base GPU $/hr'),
                Tables\Columns\TextColumn::make('benefit_margin_rate')
                    ->formatStateUsing(fn ($state) => number_format($state * 100, 0) . '%'),
                Tables\Columns\TextColumn::make('fixed_markup')
                    ->money('usd'),
                Tables\Columns\TextColumn::make('monthly_price')
                    ->money('usd')
                    ->sortable()
                    ->color('primary'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
            ])
            ->actions([
                Action::make('sync_price')
                    ->label('Recalculate Price')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (Plan $record) {
                        $calculator = app(CostCalculator::class);
                        $calculator->syncPlanFromTier($record);
                        Notification::make()
                            ->success()
                            ->title("Price recalculated: \${$record->monthly_price}/mo")
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
