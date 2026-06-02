<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;

class ClientResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Clients';
    protected static ?string $recordTitleAttribute = 'company_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Client Information')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('use_case')
                            ->maxLength(65535),
                        Forms\Components\Select::make('subscription_status')
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'paused' => 'Paused',
                                'cancelled' => 'Cancelled',
                                'past_due' => 'Past Due',
                            ]),
                    ]),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('monthly_subscription_price')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('base_monthly_cost')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('benefit_margin_rate')
                            ->numeric()
                            ->suffix('%')
                            ->formatStateUsing(fn ($state) => $state ? $state * 100 : 0)
                            ->dehydrateStateUsing(fn ($state) => $state / 100),
                        Forms\Components\TextInput::make('fixed_markup')
                            ->numeric()
                            ->prefix('$'),
                    ]),

                Forms\Components\Section::make('Financials')
                    ->schema([
                        Forms\Components\TextInput::make('actual_raw_cost_incurred')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('profit_generated')
                            ->numeric()
                            ->prefix('$'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable(),
                Tables\Columns\TextColumn::make('selected_gpu_tier')
                    ->label('GPU')
                    ->badge()
                    ->formatStateUsing(fn ($state) => config("runpod.gpu_tiers.{$state}.display", $state)),
                Tables\Columns\TextColumn::make('subscription_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'paused' => 'gray',
                        'cancelled' => 'danger',
                        'past_due' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('monthly_subscription_price')
                    ->money('usd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('profit_generated')
                    ->money('usd')
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subscription_status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'cancelled' => 'Cancelled',
                        'past_due' => 'Past Due',
                    ]),
                Tables\Filters\SelectFilter::make('selected_gpu_tier')
                    ->label('GPU Tier')
                    ->options(
                        collect(config('runpod.gpu_tiers'))
                            ->mapWithKeys(fn ($gpu, $key) => [$key => $gpu['display']])
                            ->toArray()
                    ),
            ])
            ->actions([
                Action::make('view_pods')
                    ->label('View Pods')
                    ->icon('heroicon-o-server')
                    ->url(fn (Tenant $record): string => PodResource::getUrl('index', ['tenant_id' => $record->id])),
                Action::make('impersonate')
                    ->label('Login as')
                    ->icon('heroicon-o-arrow-right-end-on-rectangle')
                    ->action(fn (Tenant $record) => redirect()->route('filament.admin.auth.login')),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListClients::route('/'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
