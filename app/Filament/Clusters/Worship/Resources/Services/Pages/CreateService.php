<?php

namespace App\Filament\Clusters\Worship\Resources\Services\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\Worship\Resources\Services\ServiceResource;
use App\Models\Person;
use App\Models\Setitem;
use Illuminate\Support\Facades\Http;

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
                $url = "https://methodist.church.net.za/api/public/preacher/" . setting('society_id') . "/" . $service->servicedate . "/" . $service->servicetime;
                try {
                    $response = Http::timeout(15)->get($url);
                    if ($response->failed()) {
                        $subtitle = '';
                    } else {
                        $preacher = $response->json('preacher');
                        if (is_array($preacher)) {
                            $subtitle = trim(($preacher['firstname'] ?? '') . ' ' . ($preacher['surname'] ?? '')) ?: '';
                            $person = Person::where('external_id',$preacher['id'])->first();
                            $service->person_id = $person->id;
                            $service->save();
                        } else {
                            $subtitle = '';
                        }
                    }
                } catch (\Throwable $e) {
                    $subtitle = '';
                }
                $ctype = 'sermon';
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
