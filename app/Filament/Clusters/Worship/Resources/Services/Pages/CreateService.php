<?php

namespace App\Filament\Clusters\Worship\Resources\Services\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\Worship\Resources\Services\ServiceResource;
use App\Models\ServicePlan;
use App\Models\Setitem;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected function afterCreate(): void
    {
        $service = $this->record;
        if ($service->setitems()->whereNotNull('content_type')->exists()) {
            return;
        }
        $settings = setting('order_of_service');
        $items = explode(',', $settings[$service->servicetime]);

        foreach ($items as $ndx => $item) {
            if ($item === 'Bible reading') {
                $subtitle = $service->reading;
                $ctype='reading';
            } elseif ($item === 'Sermon') {
                $subtitle = 'Michael Bishop';
                $ctype='sermon';
            } else {
                $subtitle = null;
                $ctype=null;
            }

            Setitem::create([
                'service_id' => $service->id,
                'title'      => $item,
                'subtitle'   => $subtitle,
                'sort_order' => $ndx,
                'content_type'=>$ctype
            ]);
        }
    }
}
