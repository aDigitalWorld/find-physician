@extends('backend.layouts.app')

@section('title', __('labels.backend.access.accounts.management') . ' | ' . __('labels.backend.access.accounts.deleted'))

@section('breadcrumb-links')
    @include('backend.accounts.includes.breadcrumb-links')
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    @lang('labels.backend.access.accounts.management')
                    <small class="text-muted">@lang('labels.backend.access.accounts.deleted')</small>
                </h4>
            </div><!--col-->
        </div><!--row-->

        <div class="row mt-4">
            <div class="col">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>City</th>
                            <th>State</th>
                            <th>Training Date</th>
                            <th>Tags</th>
                            <th>Active</th>
                            <th>@lang('labels.general.actions')</th>
                        </tr>
                        </thead>
                        <tbody>

                        @if($accounts->count())
                            @foreach($accounts as $account)
                                <tr>
                                    <td>{{ $account->name }}</td>
                                    <td>{{ $account->city }}</td>
                                    <td>{{ $account->state }}</td>
                                    <td>{{ $account->training_date }}</td>
                                    <td></td>
                                    <td>{{ $account->modified_at->diffForHumans() }}</td>
                                    <td>@include('backend.accounts.includes.actions', ['account' => $account])</td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="9"><p class="text-center">@lang('strings.backend.access.accounts.no_deleted')</p></td></tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div><!--col-->
        </div><!--row-->
        <div class="row">
            <div class="col-7">
                <div class="float-left">
                    {!! $accounts->total() !!} {{ trans_choice('labels.backend.access.accounts.table.total', $accounts->total()) }}
                </div>
            </div><!--col-->

            <div class="col-5">
                <div class="float-right">
                    {!! $accounts->render() !!}
                </div>
            </div><!--col-->
        </div><!--row-->
    </div><!--card-body-->
</div><!--card-->
@endsection
