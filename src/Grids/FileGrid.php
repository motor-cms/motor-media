<?php

namespace Motor\Media\Grids;

use Motor\Backend\Grid\Grid;

class FileGrid extends Grid
{

    protected function setup()
    {
        $this->addColumn('name');
        $this->addColumn('name');
        $this->addEditAction(trans('motor-backend::backend/global.edit'), 'backend.files.edit');
        $this->addDeleteAction(trans('motor-backend::backend/global.delete'), 'backend.files.destroy');
    }
}
