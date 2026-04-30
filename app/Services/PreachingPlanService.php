<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PreachingPlanService
{
    public function getPreacher(string $date, string $time): ?int
    {
        $url = 'https://methodist.church.net.za/preacherid/' . setting('society_id') . '/' . $time . '/' . $date;
        $response = Http::get($url);      
        if (!$response->successful()) {
            return null;
        }
        return $response->json();
    }
}

