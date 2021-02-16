@extends('backend.layouts.app')

@section('title', app_name() . ' | ' . __('strings.backend.dashboard.title'))

@push('after-styles')
<!-- DataTable Bootstrap -->
<link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.3.1/css/buttons.bootstrap.min.css">
<link rel="stylesheet" href="{{ asset('css/datatables-fix.css') }}">
@endpush

@section('content')
    <div class="row">
      <div class="col-sm-6 col-lg-3">
        <div class="card text-white bg-primary">
          <div class="card-body pb-0">
            <div class="btn-group float-right">
              <button class="btn btn-transparent dropdown-toggle p-0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="icon-settings"></i>
              </button>
            </div>
            <div class="text-value">{{ $totalAccounts }}</div>
            <div>Total Accounts</div>
          </div>
          <div class="chart-wrapper mt-3 mx-3" style="height:70px;">
            <canvas class="chart" id="card-chart1" height="70"></canvas>
          </div>
        </div>
      </div>
      <!-- /.col-->
      <div class="col-sm-6 col-lg-3">
        <div class="card text-white bg-info">
          <div class="card-body pb-0">
            <button class="btn btn-transparent p-0 float-right" type="button">
              <i class="icon-location-pin"></i>
            </button>
            <div class="text-value">{{ $totalTags }}</div>
            <div>Total Devices</div>
          </div>
          <div class="chart-wrapper mt-3 mx-3" style="height:70px;">
            <canvas class="chart" id="card-chart2" height="70"></canvas>
          </div>
        </div>
      </div>
      <!-- /.col-->
    </div>
    <!-- /.row-->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">Newest Acccounts</div>
          <div class="card-body">
            <table id="newest_accounts" class="table table-responsive-sm table-hover table-outline mb-0">
              <thead class="thead-light">
                <tr>
                  <th>Account Name</th>
                  <th class="text-center">City</th>
                  <th class="text-center">State</th>
                  <th>Tags</th>
                  <th>Status</th>
                  <th>Synced On</th>
                  <th>@lang('labels.general.actions')</th>
                </tr>
              </thead>
              <tbody>
                @foreach($accounts as $account)
                <tr>
                  <td>
                    <div><a href="{{ route('admin.account.show', [$account]) }}">{{ $account->name }}</a></div>
                  </td>
                  <td class="text-center">
                    <div>{{ $account->city }}</div>
                  </td>
                  <td class="text-center">
                    <div>{{ $account->state }}</div>
                  </td>
                  <td>{!! outputTags($account->tagNames(), true) !!}</td>
                  <td>@include('backend.accounts.includes.status', ['account' => $account])</td>
                  <td>{{ $account->getSyncedOn() }}</td>
                  <td>@include('backend.accounts.includes.actions', ['account' => $account])</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- /.col-->
    </div>
    <!-- /.row-->
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
    $('#newest_accounts').DataTable({
        "processing": false,
        "serverSide": false,
        "columns": [
            {
                "data": "name"
            }, {
                "data": "city"
            }, {
                "data": "state"
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
        aoColumnDefs: [
            {
                bSortable: false,
                aTargets: [3, -1]
            }
        ]
    });

});

</script>
@endpush
