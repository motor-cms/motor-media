<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Motor\Media\Models\File;

/**
 * Class MotorMediaBackendFileTest
 */
class MotorMediaBackendFileTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected $readPermission;

    protected $writePermission;

    protected $deletePermission;

    protected $tables = [
        'files',
        'users',
        'languages',
        'clients',
        'permissions',
        'roles',
        'user_has_permissions',
        'user_has_roles',
        'role_has_permissions',
        'media',
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/../../../../database/factories');

        $this->addDefaults();
    }

    protected function addDefaults()
    {
        $this->user = create_test_superadmin();

        $this->readPermission = create_test_permission_with_name('files.read');
        $this->writePermission = create_test_permission_with_name('files.write');
        $this->deletePermission = create_test_permission_with_name('files.delete');

        $this->actingAs($this->user);
    }

    /** @test */
    public function can_see_grid_without_file()
    {
        $this->visit('/backend/files')
            ->see(trans('motor-media::backend/files.files'))
            ->see(trans('motor-backend::backend/global.no_records'));
    }

    /** @test */
    public function can_see_grid_with_one_file()
    {
        $record = create_test_file();
        $this->visit('/backend/files')
            ->see(trans('motor-media::backend/files.files'))
            ->see($record->name);
    }

    /** @test */
    public function can_visit_the_edit_form_of_a_file_and_use_the_back_button()
    {
        $record = create_test_file();
        $this->visit('/backend/files')
            ->within('table', function () {
                $this->click(trans('motor-backend::backend/global.edit'));
            })
            ->seePageIs('/backend/files/'.$record->id.'/edit')
            ->click(trans('motor-backend::backend/global.back'))
            ->seePageIs('/backend/files');
    }

    /** @test */
    public function can_visit_the_edit_form_of_a_file_and_change_values()
    {
        $record = create_test_file();

        $this->visit('/backend/files/'.$record->id.'/edit')
            ->see($record->name)
            ->type('Updated File', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('motor-media::backend/files.save'));
            })
            ->see(trans('motor-media::backend/files.updated'))
            ->see('Updated File')
            ->seePageIs('/backend/files');

        $record = File::find($record->id);
        $this->assertEquals('Updated File', $record->name);
    }

    /** @test */
    public function can_click_the_file_create_button()
    {
        $this->visit('/backend/files')
            ->click(trans('motor-media::backend/files.new'))
            ->seePageIs('/backend/files/create');
    }

    /** @test */
    public function can_create_a_new_file()
    {
        $this->visit('/backend/files/create')
            ->see(trans('motor-media::backend/files.new'))
            ->type('Create File Name', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('motor-media::backend/files.save'));
            })
            ->see(trans('motor-media::backend/files.created'))
            ->see('Create File Name')
            ->seePageIs('/backend/files');
    }

    /** @test */
    public function cannot_create_a_new_file_with_empty_fields()
    {
        $this->visit('/backend/files/create')
            ->see(trans('motor-media::backend/files.new'))
            ->within('.box-footer', function () {
                $this->press(trans('motor-media::backend/files.save'));
            })
            ->see('Data missing!')
            ->seePageIs('/backend/files/create');
    }

    /** @test */
    public function can_modify_a_file()
    {
        $record = create_test_file();
        $this->visit('/backend/files/'.$record->id.'/edit')
            ->see(trans('motor-media::backend/files.edit'))
            ->type('Modified File Name', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('motor-media::backend/files.save'));
            })
            ->see(trans('motor-media::backend/files.updated'))
            ->see('Modified File Name')
            ->seePageIs('/backend/files');
    }

    /** @test */
    public function can_delete_a_file()
    {
        create_test_file();

        $this->assertCount(1, File::all());

        $this->visit('/backend/files')
            ->within('table', function () {
                $this->press(trans('motor-backend::backend/global.delete'));
            })
            ->seePageIs('/backend/files')
            ->see(trans('motor-media::backend/files.deleted'));

        $this->assertCount(0, File::all());
    }

    /** @test */
    public function can_paginate_file_results()
    {
        $records = create_test_file(100);
        $this->visit('/backend/files')
            ->within('.pagination', function () {
                $this->click('3');
            })
            ->seePageIs('/backend/files?page=3');
    }

    /** @test */
    public function can_search_file_results()
    {
        $records = create_test_file(10);
        $this->visit('/backend/files')
            ->type(substr($records[6]->name, 0, 3), 'search')
            ->press('grid-search-button')
            ->seeInField('search', substr($records[6]->name, 0, 3))
            ->see($records[6]->name);
    }
}
