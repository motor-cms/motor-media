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
                    axios.get('{{route('ajax.files.index')}}?category_id='+this.category_id).then(function(response) {
                        vueMediapool.files = response.data.data;
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
                axios.get('{{route('ajax.files.index')}}').then(function(response) {
                    vueMediapool.files = response.data.data;
                    // vueMediapool.$emit('test', {data: 'lol'});
                });
            }
        });
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@append
