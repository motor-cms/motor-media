@if (!isset($header) || (isset($header) && $header == true))
<div class="breadcrumb">
    {{trans('motor-media::backend/mediapool.mediapool')}}
</div>
@endif
<motor-cms-mediapool></motor-cms-mediapool>
@section('view_scripts')
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@append
