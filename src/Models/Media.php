<?php

namespace Motor\Media\Models;

use Kra8\Snowflake\HasSnowflakePrimary;

class Media extends \Spatie\MediaLibrary\MediaCollections\Models\Media
{
    use HasSnowflakePrimary;
}
