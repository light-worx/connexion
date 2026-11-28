<?php

namespace App\Presentation;

use App\Models\Service;
use Illuminate\Http\Request;

class BundleController
{
    public function show(Request $request, Service $service, SlideBuilder $builder)
    {
        abort_unless(setting('presentation_module'), 404);

        // Logged-in operators (browser) or the local app (bearer token)
        $token = (string) setting('presentation_token');
        $ok = $request->user()
            || ($token !== '' && hash_equals($token, (string) $request->bearerToken()));
        abort_unless($ok, 401);

        return response()->json($builder->build($service));
    }
}