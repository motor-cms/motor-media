<?php

namespace Motor\Media\Models;

use Kra8\Snowflake\HasShortFlakePrimary;

class Media extends \Spatie\MediaLibrary\MediaCollections\Models\Media
{
    use HasShortFlakePrimary;
}
