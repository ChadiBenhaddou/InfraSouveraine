<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PodResource\Pages;
use App\Models\Pod;
use App\Services\RunPodApi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;

class PodResource extends Resource
{
    protected static ?string $model = Pod::class;

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';
    protected static ?string $navigationGroup = 'Infrastructure';
    protected static ?string $recordTitleAttribute = 'runpod_pod_id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pod Information')
                    ->schema([
                        Forms\Components\TextInput::make('runpod_pod_id')->label('RunPod ID'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'CREATING' => 'Creating',
                                'INITIALIZING' => 'Initializing',
                                'RUNNING' => 'Running',
                                'STOPPED' => 'Stopped',
                                'TERMINATED' => 'Terminated',
                                'FAILED' => 'Failed',
                            ]),
                        Forms\Components\TextInput::make('gpu_tier'),
                        Forms\Components\TextInput::make('model_id'),
                    ]),

                Forms\Components\Section::make('Network')
                    ->schema([
                        Forms\Components\TextInput::make('public_ip'),
                        Forms\Components\TextInput::make('port'),
                        Forms\Components\TextInput::make('webui_url'),
                    ]),

                Forms\Components\Section::make('Cost & Metrics')
                    ->schema([
                        Forms\Components\TextInput::make('cost_incurred')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\Textarea::make('runtime_metrics')
                            ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tenant.company_name')
                    ->label('Client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('runpod_pod_id')
                    ->label('RunPod ID')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'RUNNING' => 'success',
                        'CREATING', 'INITIALIZING' => 'warning',
                        'STOPPED' => 'gray',
                        'TERMINATED', 'FAILED' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('gpu_tier')
                    ->label('GPU'),
                Tables\Columns\TextColumn::make('model_id')
                    ->label('Model')
                    ->formatStateUsing(fn ($state) => config("runpod.recommended_models.{$state}.display", $state)),
                Tables\Columns\TextColumn::make('cost_incurred')
                    ->money('usd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('provisioned_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'CREATING' => 'Creating',
                        'INITIALIZING' => 'Initializing',
                        'RUNNING' => 'Running',
                        'STOPPED' => 'Stopped',
                        'TERMINATED' => 'Terminated',
                        'FAILED' => 'Failed',
                    ]),
            ])
            ->actions([
                Action::make('refresh_status')
                    ->label('Refresh Status')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (Pod $record) {
                        try {
                            $api = app(RunPodApi::class);
                            $response = $api->getPod($record->runpod_pod_id);
                            $status = $response['pod']['status'] ?? $response['status'] ?? 'UNKNOWN';
                            $record->update([
                                'status' => $status,
                                'runtime_metrics' => $response,
                                'last_active_at' => now(),
                            ]);
                            Notification::make()->success()->title("Pod status: {$status}")->send();
                        } catch (\Throwable $e) {
                            Notification::make()->danger()->title("Failed: {$e->getMessage()}")->send();
                        }
                    }),
                Action::make('restart')
                    ->label('Restart')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Pod $record) {
                        try {
                            $api = app(RunPodApi::class);
                            $record->update(['status' => 'STOPPED']);
                            $api->stopPod($record->runpod_pod_id);
                            sleep(2);
                            $api->startPod($record->runpod_pod_id);
                            $record->update(['status' => 'INITIALIZING']);
                            Notification::make()->success()->title('Pod restart initiated')->send();
                        } catch (\Throwable $e) {
                            Notification::make()->danger()->title("Failed: {$e->getMessage()}")->send();
                        }
                    }),
                Action::make('stop')
                    ->label('Pause')
                    ->icon('heroicon-o-pause')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (Pod $record) {
                        try {
                            $api = app(RunPodApi::class);
                            $api->stopPod($record->runpod_pod_id);
                            $record->update(['status' => 'STOPPED', 'last_active_at' => now()]);
                            Notification::make()->success()->title('Pod paused')->send();
                        } catch (\Throwable $e) {
                            Notification::make()->danger()->title("Failed: {$e->getMessage()}")->send();
                        }
                    }),
                Action::make('terminate')
                    ->label('Terminate')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Pod $record) {
                        try {
                            $api = app(RunPodApi::class);
                            $api->terminatePod($record->runpod_pod_id);
                            $record->update(['status' => 'TERMINATED']);
                            Notification::make()->success()->title('Pod terminated')->send();
                        } catch (\Throwable $e) {
                            Notification::make()->danger()->title("Failed: {$e->getMessage()}")->send();
                        }
                    }),
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
            'index' => Pages\ListPods::route('/'),
            'view' => Pages\ViewPod::route('/{record}'),
        ];
    }
}
