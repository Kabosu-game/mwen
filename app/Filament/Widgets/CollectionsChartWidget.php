<?php

namespace App\Filament\Widgets;

use App\Models\CollectionRequest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class CollectionsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Demandes de ramassage — 30 derniers jours';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d/m');
            $data[] = CollectionRequest::whereDate('created_at', $date)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Demandes',
                    'data' => $data,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
