@extends('backend.layouts.app')

@section('title', app_name() . ' | ' . __('labels.backend.access.accounts.management'))

@section('breadcrumb-links')
    @include('backend.accounts.includes.breadcrumb-links')
@endsection

@push('after-styles')
<!-- DataTable Bootstrap -->
<link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.3.1/css/buttons.bootstrap.min.css">
<link rel="stylesheet" href="{{ asset('css/datatables-fix.css') }}">
@endpush

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    Account Management <small class="text-muted">{{ __('labels.backend.access.accounts.active') }}</small>
                </h4>
            </div><!--col-->

            <div class="col-sm-7">
                @include('backend.accounts.includes.header-buttons')
            </div><!--col-->
        </div><!--row-->

        <div class="row mt-4">
            <div class="col">
                <div class="table-responsive">
                    <table class="table" id="accounts_table">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>City</th>
                            <th>State</th>
                            <th>Country</th>
                            <th>Training Date</th>
                            <th>Tags</th>
                            <th>Active</th>
                            <th>Modified At</th>
                            <th>@lang('labels.general.actions')</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div><!--col-->
        </div><!--row-->

    </div><!--card-body-->
</div><!--card-->
@endsection

@push('after-scripts')
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<!--
<script src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.colVis.min.js"></script>
<script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>
-->
<script>
$(function () {
    $('#accounts_table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "{{ route('admin.accounts.list') }}",
            "dataType": "json",
            "type": "POST",
            "data": {
                _token: $('meta[name=csrf-token]').attr("content"),
                route: 'accounts'
            }
        },
        "columns": [
            {
                "data": "name"
            }, {
                "data": "city"
            }, {
                "data": "state"
            }, {
                "data": "country"
            }, {
                "data": "training_date"
            }, {
                "data": "tags"
            }, {
                "data": "active"
            }, {
                "data": "modified_at"
            },
            {
                "data": "action"
            }
        ],
/*
        "fnRowCallback": function(nRow, aData, iDisplayIndex) {
            console.log(nRow,aData,iDisplayIndex);
        },
*/
        aoColumnDefs: [
            {
                bSortable: false,
                aTargets: [4, -1]
            }
        ]
    });

});

</script>
@endpush
