<?php
/**
 * @param int $count
 * @return mixed
 */
function create_test_file($count = 1)
{
    return factory(Motor\Media\Models\File::class, $count)->create();
}
