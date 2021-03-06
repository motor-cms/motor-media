{!! form_start($form, ['id' => 'file-form']) !!}
<div class="row">
    <div class="col-md-8">
        <div class="@boxWrapper box-primary">
            <div class="@boxHeader with-border">
                <h3 class="box-title">{{ trans('motor-backend::backend/global.base_info') }}</h3>
            </div>
            <div class="@boxBody">
                {!! form_until($form, 'file') !!}
            </div>
            <!-- /.box-body -->

            <div class="@boxFooter">
                {!! form_row($form->submit) !!}
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="@boxWrapper box-primary">
            <div class="@boxHeader with-border">
                <h3 class="box-title">{{ trans('motor-backend::backend/category_trees.category_tree') }}</h3>
            </div>
            <div class="@boxBody">
                <div id="category-tree-errors" class="d-none">
                    <div class="alert alert-danger" role="alert">
                        {{trans('motor-media::backend/files.please_select_category')}}
                    </div>
                </div>
                <div id="category-tree">
                    @include('motor-backend::layouts.partials.category-tree-items', array('items' => $trees, 'newItem' => $newItem, 'selectedItem' => $selectedItem))
                </div>
            </div>
        </div>
    </div>

</div>
{!! form_end($form) !!}
@section('view_scripts')
    <script>
        $.jstree.defaults.checkbox.three_state = false;

        let tree = $('#category-tree').jstree(
            {
                "plugins": ["checkbox"]
            }
        );

        tree.jstree('open_all');

        // Check all nodes
        @if (isset($record))
        @foreach ($record->categories()->get() as $category)
            tree.jstree().check_node($('*[data-category-id="{{$category->id}}"]'));
        @endforeach
        @endif

        $('#j1_1').find('.jstree-checkbox').first().hide();

        $('form#file-form').on('submit', function (e) {
            $('#category-tree-errors').addClass('d-none');
            let categories = [];

            $.each(tree.jstree().get_checked('full'), function(key, item) {
                categories.push(item.data.categoryId);
            });

            if (categories.length === 0) {
                $('#category-tree-errors').removeClass('d-none');
                e.preventDefault();
            }

            $('input[name="categories"]').val(categories.join());
        });
    </script>
@append
