<?php

namespace Motor\Media\Http\Controllers\Backend;

use Motor\Backend\Http\Controllers\Controller;

use Motor\Backend\Models\Category;
use Motor\Media\Models\File;
use Motor\Media\Http\Requests\Backend\FileRequest;
use Motor\Media\Services\FileService;
use Motor\Media\Grids\FileGrid;
use Motor\Media\Forms\Backend\FileForm;

use Kris\LaravelFormBuilder\FormBuilderTrait;

class FilesController extends Controller
{
    use FormBuilderTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $grid = new FileGrid(File::class);

        $service = FileService::collection($grid);
        $grid->filter = $service->getFilter();
        $paginator    = $service->getPaginator();

        return view('motor-media::backend.files.index', compact('paginator', 'grid'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $form = $this->form(FileForm::class, [
            'method'  => 'POST',
            'route'   => 'backend.files.store',
            'enctype' => 'multipart/form-data'
        ]);

        $trees = Category::where('scope', 'media')->defaultOrder()->get()->toTree();
        $newItem = false;
        $selectedItem = null;

        return view('motor-media::backend.files.create', compact('form', 'trees', 'newItem', 'selectedItem', 'root'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(FileRequest $request)
    {
        $form = $this->form(FileForm::class);

        // It will automatically use current request, get the rules, and do the validation
        if ( ! $form->isValid()) {
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }

        FileService::createWithForm($request, $form);

        flash()->success(trans('motor-media::backend/files.created'));

        return redirect('backend/files');
    }


    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(File $record)
    {
        $form = $this->form(FileForm::class, [
            'method'  => 'PATCH',
            'url'     => route('backend.files.update', [ $record->id ]),
            'enctype' => 'multipart/form-data',
            'model'   => $record
        ]);

        $trees = Category::where('scope', 'media')->defaultOrder()->get()->toTree();
        $newItem = false;
        $selectedItem = null;

        return view('motor-media::backend.files.edit', compact('form', 'trees', 'newItem', 'selectedItem', 'root', 'record'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(FileRequest $request, File $record)
    {
        $form = $this->form(FileForm::class);

        // It will automatically use current request, get the rules, and do the validation
        if ( ! $form->isValid()) {
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }

        FileService::updateWithForm($record, $request, $form);

        flash()->success(trans('motor-media::backend/files.updated'));

        return redirect('backend/files');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $record)
    {
        FileService::delete($record);

        flash()->success(trans('motor-media::backend/files.deleted'));

        return redirect('backend/files');
    }
}
