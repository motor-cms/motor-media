<style type="text/css">
    .sortable-ghost {
        opacity: 0.7;
    }
    .sortable-drag {
        opacity: 0.5;
    }
    .sortable-drag .card-body {
        display: none;
    }
</style>
@if (!isset($header) || (isset($header) && $header == true))
<div class="breadcrumb">
    {{trans('motor-media::backend/mediapool.mediapool')}}
</div>
@endif
<div id="mediapool" class="container" style="overflow:scroll; position: absolute; top: 50px; bottom: 0;">
    <div class="form-group">
        <label class="control-label">
            {{ trans('motor-backend::backend/categories.category') }}
        </label>
        <select class="form-control" name="category_id" v-model="category_id" @change="refreshFiles">
            <option value="">{{ trans('motor-backend::backend/categories.all_categories') }}</option>
            <option v-for="(category, index) in categories" :value="category.id">
                @{{ category.name }}
            </option>
        </select>
    </div>
    <button type="button" class="btn btn-sm btn-primary float-right" @click="next" v-if="pagination && pagination.total_pages > 1 && pagination.current_page < pagination.total_pages"> >> </button>
    <button type="button" class="btn btn-sm btn-primary float-left" @click="previous" v-if="pagination && pagination.total_pages > 1 && (pagination.current_page >= pagination.total_pages || (pagination.current_page > 1 && pagination.current_page < pagination.total_pages))"> << </button>
    <div class="clearfix mb-2"></div>
    <draggable v-model="files" :options="{group:{ name:'files',  pull:'clone', put:false }, sort: false, dragClass: 'sortable-drag', ghostClass: 'sortable-ghost'}" @start="onStart" @end="onEnd">
        <div v-for="file in files">
            <div class="card">
                <img v-if="isImage(file)" class="card-img-top" :src="file.file.preview">
                <div class="card-body" data-toggle="tooltip" data-placement="top" :title="file.description">
                    <p class="card-text">
                        @{{ file.file.file_name }}<br>
                        {{--@{{ file.description }}<br>--}}
                        <span class="badge badge-secondary badge-pill">@{{ file.file.mime_type }}</span>
                    </p>
                </div>
            </div>
        </div>
    </draggable>


    {{--<div class="file" v-for="file in files">--}}
    {{--</div>--}}
</div>
@section('view_scripts')
    <script>

        Vue.component('files-view', {
            template: '<div><file-view></file-view></div>'
        });
        Vue.component('file-view', {

        });
        var vueMediapool = new Vue({
            el: '#mediapool',
            data: {
                files: [],
                categories: [],
                category_id: '',
                pagination: false,
            },
            components: {
                draggable,
            },
            methods: {
                onStart: function(e) {
                    this.$emit('mediapool:drag:start', true);
                },
                onEnd: function(e) {
                    this.$emit('mediapool:drag:end', true);
                },
                refreshFiles: function() {
                    axios.get('{{route('ajax.files.index')}}?sortable_field=created_at&sortable_direction=DESC&category_id='+this.category_id).then(function(response) {
                        vueMediapool.files = response.data.data;
                        vueMediapool.pagination = response.data.meta.pagination;
                    });
                },
                next: function() {
                    console.log("PAGINATE");
                    axios.get('{{route('ajax.files.index')}}?sortable_field=created_at&sortable_direction=DESC&category_id='+this.category_id+'&page='+(vueMediapool.pagination.current_page+1)).then(function(response) {
                        vueMediapool.files = response.data.data;
                        vueMediapool.pagination = response.data.meta.pagination;
                    });
                },
                previous: function() {
                    axios.get('{{route('ajax.files.index')}}?sortable_field=created_at&sortable_direction=DESC&category_id='+this.category_id+'&page='+(vueMediapool.pagination.current_page-1)).then(function(response) {
                        vueMediapool.files = response.data.data;
                        vueMediapool.pagination = response.data.meta.pagination;
                    });
                },
                isImage: function(file) {
                    if (file.file.mime_type == 'image/png' || file.file.mime_type == 'image/jpg' || file.file.mime_type == 'video/mp4') {
                        return true;
                    }
                    return false;
                }
            },
            mounted: function () {

                axios.get('{{route('ajax.categories.index')}}?scope=media').then(function(response) {
                    vueMediapool.categories = response.data.data;

                    // vueMediapool.$emit('test', {data: 'lol'});
                });
                axios.get('{{route('ajax.files.index')}}?sortable_field=created_at&sortable_direction=DESC').then(function(response) {
                    vueMediapool.files = response.data.data;
                    vueMediapool.pagination = response.data.meta.pagination;
                    // vueMediapool.$emit('test', {data: 'lol'});
                });
            }
        });
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@append
