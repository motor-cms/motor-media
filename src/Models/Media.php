<?php

namespace Motor\Media\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Media extends \Spatie\MediaLibrary\MediaCollections\Models\Media
{
    use HasUuids;
}
