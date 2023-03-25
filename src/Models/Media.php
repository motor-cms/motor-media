<?php

namespace Motor\Media\Models;

use Kra8\Snowflake\HasShortflakePrimary;

class Media extends \Spatie\MediaLibrary\MediaCollections\Models\Media
{
    use HasShortflakePrimary;
}
