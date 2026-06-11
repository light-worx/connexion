<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Setitem;
use App\Models\Person;
use Illuminate\Support\Facades\Http;

class OrderOfServiceService
{
    /**
     * Populate setitems for a service using the standard order_of_service setting.
     * Mirrors the logic in CreateService::afterCreate().
     *
     * Safe to call on a new service — skips if the service already has content items.
     */
    public function populate(Service $service): void
    {
        // Don't overwrite an existing order of service
        if ($service->setitems()->whereNotNull('content_type')->exists()) {
            return;
        }

        $settings = setting('order_of_service');

        if (empty($settings) || empty($settings[$service->servicetime])) {
            \Log::warning("OrderOfServiceService: no order_of_service setting found for time {$service->servicetime}");
            return;
        }

        $items = explode(',', $settings[$service->servicetime]);

        foreach ($items as $ndx => $item) {
            $item     = trim($item);
            $subtitle = null;
            $ctype    = null;

            if ($item === 'Bible reading') {
                $subtitle = $service->reading;
                $ctype    = 'reading';

            } elseif ($item === 'Sermon') {
                $ctype    = 'sermon';
                $subtitle = '';

                $url = "https://methodist.church.net.za/api/public/preacher/"
                    . setting('society_id') . "/"
                    . $service->servicedate . "/"
                    . $service->servicetime;

                try {
                    $response = Http::timeout(15)->get($url);

                    if ($response->successful()) {
                        $preacher = $response->json('preacher');

                        if (is_array($preacher)) {
                            $subtitle = trim(($preacher['firstname'] ?? '') . ' ' . ($preacher['surname'] ?? '')) ?: '';

                            $person = Person::where('external_id', $preacher['id'])->first();
                            if ($person) {
                                $service->person_id = $person->id;
                                $service->save();
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::warning("OrderOfServiceService: preacher API failed — {$e->getMessage()}");
                }
            }

            Setitem::create([
                'service_id'   => $service->id,
                'title'        => $item,
                'subtitle'     => $subtitle,
                'sort_order'   => $ndx,
                'content_type' => $ctype,
            ]);
        }
    }
}