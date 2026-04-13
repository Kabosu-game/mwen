<?php

namespace App\Filament\Widgets;

use App\Models\CollectionRequest;
use App\Models\Mission;
use App\Models\Report;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayCollections = CollectionRequest::whereDate('created_at', today())->count();
        $pendingCollections = CollectionRequest::where('status', 'pending')->count();
        $activeCollectors = User::collectors()->where('status', 'active')->count();
        $criticalReports = Report::whereIn('severity', ['high', 'critical'])->where('status', 'pending')->count();
        $openMissions = Mission::where('status', 'open')->count();
        $totalCitizens = User::citizens()->where('status', 'active')->count();

        return [
            Stat::make('Citoyens actifs', number_format($totalCitizens))
                ->description('Inscrits sur l\'application')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->chart(
                    User::citizens()
                        ->where('created_at', '>=', now()->subDays(7))
                        ->selectRaw('COUNT(*) as count, DATE(created_at) as date')
                        ->groupBy('date')
                        ->pluck('count')
                        ->toArray()
                ),

            Stat::make('Demandes en attente', $pendingCollections)
                ->description($todayCollections . ' nouvelles aujourd\'hui')
                ->descriptionIcon('heroicon-m-truck')
                ->color($pendingCollections > 10 ? 'danger' : 'warning'),

            Stat::make('Collecteurs actifs', $activeCollectors)
                ->description('Disponibles sur le terrain')
                ->descriptionIcon('heroicon-m-identification')
                ->color('success'),

            Stat::make('Signalements critiques', $criticalReports)
                ->description('À traiter en urgence')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($criticalReports > 0 ? 'danger' : 'success'),

            Stat::make('Missions ouvertes', $openMissions)
                ->description('Travay Vèt disponibles')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('info'),

            Stat::make('Collectes terminées', CollectionRequest::where('status', 'completed')->whereMonth('created_at', now()->month)->count())
                ->description('Ce mois-ci')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
