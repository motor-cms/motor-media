@if (!isset($header) || (isset($header) && $header == true))
<div class="breadcrumb">
    {{trans('motor-media::backend/mediapool.mediapool')}}
</div>
@endif
<motor-media-mediapool></motor-media-mediapool>
@section('view_scripts')
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@append
