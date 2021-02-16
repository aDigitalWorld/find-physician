@extends('backend.layouts.app')

@section('title', __('labels.backend.access.accounts.management') . ' | ' . __('labels.backend.access.accounts.view'))

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
                    <small class="text-muted">@lang('labels.backend.access.accounts.view')</small>
                </h4>
            </div><!--col-->
        </div><!--row-->

        <div class="row mt-4 mb-4">
            <div class="col">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-expanded="true"><i class="fas fa-account"></i> @lang('labels.backend.access.accounts.tabs.titles.overview')</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="overview" role="tabpanel" aria-expanded="true">
                        @include('backend.accounts.show.tabs.overview')
                    </div><!--tab-->
                </div><!--tab-content-->
            </div><!--col-->
        </div><!--row-->
    </div><!--card-body-->

    <div class="card-footer">
        <div class="row">
            <div class="col">
                <small class="float-right text-muted">
                    <strong>@lang('labels.backend.access.accounts.tabs.content.overview.created_at'):</strong> {{ $account->created_at->diffForHumans() }},
                    @if($account->trashed())
                        <strong>@lang('labels.backend.access.accounts.tabs.content.overview.deleted_at'):</strong> {{ $account->deleted_at->diffForHumans() }}
                    @endif
                </small>
            </div><!--col-->
        </div><!--row-->
    </div><!--card-footer-->
</div><!--card-->
@endsection
