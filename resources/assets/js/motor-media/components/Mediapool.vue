<template>
  <div id="mediapool" class="container mt-3" :class="[componentModal ? 'component-modal' : '']">
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
    <div class="flex">
      <button type="button" class="btn btn-sm btn-primary flex-row-reverse" @click="next"
              v-if="pagination && pagination.last_page > 1 && pagination.current_page < pagination.last_page"> >>
      </button>
      <button type="button" class="btn btn-sm btn-primary flex-row" @click="previous"
              v-if="pagination && pagination.last_page > 1 && (pagination.current_page >= pagination.last_page || (pagination.current_page > 1 && pagination.current_page < pagination.last_page))">
        <<
      </button>
    </div>
    <div class="clearfix mb-2"></div>
    <draggable v-model="files"
               :options="{group:{ name:'files',  pull:'clone', put:false }, sort: false, dragClass: 'sortable-drag', ghostClass: 'sortable-ghost'}"
               @start="onStart" @end="onEnd">
      <div v-for="file in files">
        <div class="card">
          <img v-if="isImage(file) && file.exists" class="card-img-top" :src="file.file.conversions.preview">
          <div class="card-body" data-toggle="tooltip" data-placement="top" :title="file.description">
            <p class="card-text">
              <b v-if="!file.exists">File not found!<br></b>
              {{ file.file.file_name }}<br>
              {{ file.description }}<br>
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
      axios.get(this.route('ajax.files.index') + '?sortable_field=created_at&sortable_direction=DESC&category_id=' + this.category_id).then((response) => {
        this.files = response.data.data;
        this.pagination = response.data.meta;
      });
    },
    next: function () {
      console.log("PAGINATE");
      axios.get(this.route('ajax.files.index') + '?sortable_field=created_at&sortable_direction=DESC&category_id=' + this.category_id + '&page=' + (this.pagination.current_page + 1)).then((response) => {
        this.files = response.data.data;
        this.pagination = response.data.meta;
      });
    },
    previous: function () {
      axios.get(this.route('ajax.files.index') + '?sortable_field=created_at&sortable_direction=DESC&category_id=' + this.category_id + '&page=' + (this.pagination.current_page - 1)).then((response) => {
        this.files = response.data.data;
        this.pagination = response.data.meta;
      });
    },
    isImage: function (file) {
      if (file.file.mime_type === 'image/png' || file.file.mime_type === 'image/jpg' || file.file.mime_type === 'image/jpeg' || file.file.mime_type === 'video/x-m4v' || file.file.mime_type === 'video/mp4') {
        return true;
      }
      return false;
    }
  },
  mounted: function () {
    axios.get(this.route('ajax.categories.index') + '?scope=media').then((response) => {
      this.categories = response.data.data;

      // vueMediapool.$emit('test', {data: 'lol'});
    });
    axios.get(this.route('ajax.files.index') + '?sortable_field=created_at&sortable_direction=DESC').then((response) => {
      this.files = response.data.data;
      this.pagination = response.data.meta;
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
  top: 0;
  bottom: 0;
  right: 0;
  left: 0;
}

#mediapool.component-modal {
  top: 0;
}
</style>
