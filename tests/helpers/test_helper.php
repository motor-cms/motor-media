<?php
function create_test_file($count = 1)
{
    return factory(Motor\Media\Models\File::class, $count)->create();
}
