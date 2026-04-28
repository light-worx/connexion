<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PreachingPlanService
{
    // https://methodist.church.net.za/preacher/697/09h00/2026-05-03
    public function getPreacher(string $date, string $time): ?array
    {
        $response = Http::get('https://methodist.church.net.za/preacherid/', [
            'society' => setting('society_id'),
            'date' => $date,
            'time' => $time,
        ]);

        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    }
}

