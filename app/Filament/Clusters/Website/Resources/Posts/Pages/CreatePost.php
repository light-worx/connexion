<?php

namespace App\Filament\Clusters\Website\Resources\Posts\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\Website\Resources\Posts\PostResource;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;
}
