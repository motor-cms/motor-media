<template>
    <div id="mediapool" class="container" :class="[componentModal ? 'component-modal' : '']">
        <div class="form-group">
            <label class="control-label">
                {{ $t('motor-backend.backend.categories.category') }}
            </label>
            <select class="form-control" name="category_id" v-model="category_id" @change="refreshFiles">
                <option value="">{{ $t('motor-backend.backend.categories.all_categories') }}</option>
                <option v-for="(category, index) in categories" :value="category.id">
                    {{ category.name }}
                </option>
            </select>
        </div>
        <button type="button" class="btn btn-sm btn-primary float-right" @click="next"
                v-if="pagination && pagination.total_pages > 1 && pagination.current_page < pagination.total_pages"> >>
        </button>
        <button type="button" class="btn btn-sm btn-primary float-left" @click="previous"
                v-if="pagination && pagination.total_pages > 1 && (pagination.current_page >= pagination.total_pages || (pagination.current_page > 1 && pagination.current_page < pagination.total_pages))">
            <<
        </button>
        <div class="clearfix mb-2"></div>
        <draggable v-model="files"
                   :options="{group:{ name:'files',  pull:'clone', put:false }, sort: false, dragClass: 'sortable-drag', ghostClass: 'sortable-ghost'}"
                   @start="onStart" @end="onEnd">
            <div v-for="file in files">
                <div class="card">
                    <img v-if="isImage(file)" class="card-img-top" :src="file.file.preview">
                    <div class="card-body" data-toggle="tooltip" data-placement="top" :title="file.description">
                        <p class="card-text">
                            {{ file.file.file_name }}<br>
                            <!--{{ file.description }}<br>-->
                            <span class="badge badge-secondary badge-pill">{{ file.file.mime_type }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </draggable>
    </div>
</template>

<style lang="scss">
    .sortable-ghost {
        opacity: 0.7;
    }

    .sortable-drag {
        opacity: 0.5;
    }

    .sortable-drag .card-body {
        /*display: none;*/
    }

</style>

<script>
    import draggable from 'vuedraggable';
    import {Ziggy} from 'ziggy-js';
    import route from 'ziggy-js/src/js/route';

    window.Ziggy = Ziggy;

    Vue.mixin({
        methods: {
            route: route
        }
    });


    export default {
        name: 'motor-media-mediapool',
        props: ['componentModal'],
        data: function () {
            return {
                files: [],
                categories: [],
                category_id: '',
                pagination: false,
            }
        },
        components: {
            draggable,
        },
        methods: {
            onStart: function (e) {
                this.$eventHub.$emit('mediapool:drag:start', true);
            },
            onEnd: function (e) {
                this.$eventHub.$emit('mediapool:drag:end', true);
            },
            refreshFiles: function () {
                axios.get(route('ajax.files.index') + '?sortable_field=created_at&sortable_direction=DESC&category_id=' + this.category_id).then((response) => {
                    this.files = response.data.data;
                    this.pagination = response.data.meta.pagination;
                });
            },
            next: function () {
                console.log("PAGINATE");
                axios.get(route('ajax.files.index') + '?sortable_field=created_at&sortable_direction=DESC&category_id=' + this.category_id + '&page=' + (this.pagination.current_page + 1)).then((response) => {
                    this.files = response.data.data;
                    this.pagination = response.data.meta.pagination;
                });
            },
            previous: function () {
                axios.get(route('ajax.files.index') + '?sortable_field=created_at&sortable_direction=DESC&category_id=' + this.category_id + '&page=' + (this.pagination.current_page - 1)).then((response) => {
                    this.files = response.data.data;
                    this.pagination = response.data.meta.pagination;
                });
            },
            isImage: function (file) {
                if (file.file.mime_type == 'image/png' || file.file.mime_type == 'image/jpg' || file.file.mime_type == 'image/jpeg' || file.file.mime_type == 'video/mp4') {
                    return true;
                }
                return false;
            }
        },
        mounted: function () {
            axios.get(route('ajax.categories.index') + '?scope=media').then((response) => {
                this.categories = response.data.data;

                // vueMediapool.$emit('test', {data: 'lol'});
            });
            axios.get(route('ajax.files.index') + '?sortable_field=created_at&sortable_direction=DESC').then((response) => {
                this.files = response.data.data;
                this.pagination = response.data.meta.pagination;
                // vueMediapool.$emit('test', {data: 'lol'});
            });
        },
    }
</script>


<style lang="scss">
    #mediapool.component-modal,
    #mediapool {
        overflow: scroll;
        position: absolute;
        width: 100%;
        top: 50px;
        bottom: 0;
        right: 0;
        left: 0;
    }
    #mediapool.component-modal {
        top: 0;
    }
</style>
