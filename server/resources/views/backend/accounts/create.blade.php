@extends('backend.layouts.app')

@section('title', __('labels.backend.access.accounts.management') . ' | ' . __('labels.backend.access.accounts.create'))

@section('breadcrumb-links')
    @include('backend.accounts.includes.breadcrumb-links')
@endsection

@push('after-styles')
    <link href="https://unpkg.com/gijgo@1.9.13/css/gijgo.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://res.cloudinary.com/dxfq3iotg/raw/upload/v1569006288/BBBootstrap/choices.min.css?version=7.0.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" />
    <style>
        .choices__list--dropdown { z-index: 10; }
        #hidden { display: none; }
        .has-error label,
        .has-error input,
        .has-error textarea {
            color: red;
            border-color: red;
        }

        .list-unstyled li {
            font-size: 13px;
            padding: 4px 0 0;
            color: red;
        }
    </style>
@endpush

@section('content')
    {{ html()->form('POST', route('admin.accounts.store'))->class('form-horizontal needs-validation')->open() }}
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-5">
                        <h4 class="card-title mb-0">
                            @lang('labels.backend.access.accounts.management')
                            <small class="text-muted">@lang('labels.backend.access.accounts.create')</small>
                        </h4>
                    </div><!--col-->
                </div><!--row-->

                <hr>

                <div class="row mt-4 mb-4">
                    <div class="col">
                        <div class="form-group row">
                            {{ html()->label(__('validation.attributes.backend.access.accounts.name'))->class('col-md-2 form-control-label')->for('name') }}

                            <div class="col-sm-10 col-md-6">
                                {{ html()->text('name')
                                    ->class('form-control')
                                    ->placeholder(__('validation.attributes.backend.access.accounts.name'))
                                    ->attribute('maxlength', 191)
                                    ->required()
                                    ->autofocus() }}

                                <div class="help-block with-errors">
                                </div>
                            </div><!--col-->
                        </div><!--form-group-->

                        <div class="form-group row">
                        {{ html()->label(__('validation.attributes.backend.access.accounts.street'))->class('col-md-2 form-control-label')->for('street') }}

                            <div class="col-sm-10 col-md-6">
                                {{ html()->text('street')
                                    ->class('form-control')
                                    ->placeholder(__('validation.attributes.backend.access.accounts.street'))
                                    ->attribute('maxlength', 191)
                                    ->required() }}
                                <div class="help-block with-errors">
                                </div>
                            </div><!--col-->
                        </div><!--form-group-->


                        <div class="form-group row">
                            {{ html()->label(__('validation.attributes.backend.access.accounts.country'))->class('col-md-2 form-control-label')->for('country') }}

                            <div class="col-sm-10 col-md-6">
                               {{ html()->select('country')
                                   ->class('form-control')
                                   ->options(getCountry())
                                   ->placeholder(__('validation.attributes.backend.access.accounts.country'))
                                   ->required() }}
                                <div class="help-block with-errors">
                                </div>
                            </div><!--col-->
                        </div><!--form-group-->

                        <div class="form-group row">
                            {{ html()->label(__('validation.attributes.backend.access.accounts.city'))->class('col-md-2 form-control-label')->for('city') }}

                            <div class="col-sm-10 col-md-6">
                                {{ html()->text('city')
                                    ->class('form-control')
                                    ->placeholder(__('validation.attributes.backend.access.accounts.city'))
                                    ->attribute('maxlength', 191)
                                    ->required() }}
                                <div class="help-block with-errors">
                                </div>
                            </div><!--col-->
                        </div><!--form-group-->

                        <div class="form-group row hidden" id="stateRow">
                            {{ html()->label(__('validation.attributes.backend.access.accounts.state'))->class('col-md-2 form-control-label')->for('state') }}

                            <div class="col-sm-10 col-md-6">
                               {{ html()->select('hidden')
                                   ->class('form-control hidden')
                                   ->options(getStates()) }}
                               {{ html()->select('state')
                                   ->class('form-control')
                                   ->options(getStates())
                                   ->placeholder(__('validation.attributes.backend.access.accounts.state'))
                                   ->required() }}
                                <div class="help-block with-errors">
                                </div>
                            </div><!--col-->
                        </div><!--form-group-->

                        <!--
                        <div class="form-group row hidden" id="providencesRow">
                            {{ html()->label(__('validation.attributes.backend.access.accounts.providences'))->class('col-md-2 form-control-label')->for('state') }}

                            <div class="col-sm-10 col-md-6">
                               {{ html()->select('providences')
                                   ->class('form-control hidden')
                                   ->options(getProvidences())
                                   ->placeholder(__('validation.attributes.backend.access.accounts.providences'))
                                   ->required() }}
                                <div class="help-block with-errors">
                                </div>
                            </div><!--col-->
                        <!--
                        </div><!--form-group-->

                        <div class="form-group row">
                            {{ html()->label(__('validation.attributes.backend.access.accounts.zipcode'))->class('col-md-2 form-control-label')->for('zipcode') }}

                            <div class="col-sm-10 col-md-6">
                                {{ html()->text('zipcode')
                                    ->class('form-control')
                                    ->placeholder(__('validation.attributes.backend.access.accounts.zipcode'))
                                    ->attribute('maxlength', 15)
                                    ->required() }}
                                <div class="help-block with-errors">
                                </div>
                            </div><!--col-->
                        </div><!--form-group-->

                        <div class="form-group row">
                            {{ html()->label(__('validation.attributes.backend.access.accounts.phone'))->class('col-md-2 form-control-label')->for('phone') }}

                            <div class="col-sm-10 col-md-3">
                                {{ html()->text('phone')
                                    ->class('form-control')
                                    ->placeholder(__('validation.attributes.backend.access.accounts.phone'))
                                    ->attribute('maxlength', 20) }}
                                <div class="help-block with-errors">
                                </div>
                            </div><!--col-->
                        </div><!--form-group-->

                        <div class="form-group row">
                            {{ html()->label(__('validation.attributes.backend.access.accounts.website'))->class('col-md-2 form-control-label')->for('website') }}

                            <div class="col-sm-10 col-md-6">
                                {{ html()->text('website')
                                    ->class('form-control')
                                    ->placeholder(__('validation.attributes.backend.access.accounts.website'))
                                    ->attribute('maxlength', 191) }}
                                <div class="help-block with-errors">
                                </div>
                            </div><!--col-->
                        </div><!--form-group-->

                        <div class="form-group row">
                            {{ html()->label(__('validation.attributes.backend.access.accounts.training_date'))->class('col-md-2 form-control-label')->for('training_date') }}

                            <div class="col-sm-10 col-md-3">
                                {{ html()->text('training_date')
                                    ->id('training_date')
                                    ->class('form-control')
                                    ->placeholder(__('validation.attributes.backend.access.accounts.training_date'))
                                    ->attribute('maxlength', 30) }}
                                <div class="help-block with-errors">
                                </div>
                            </div><!--col-->
                        </div><!--form-group-->

                        <div class="form-group row">
                            {{ html()->label(__('validation.attributes.backend.access.accounts.active'))->class('col-md-2 form-control-label')->for('active') }}

                            <div class="col-sm-10 col-md-6">
                                <label class="switch switch-label switch-pill switch-primary">
                                    {{ html()->checkbox('active', true)->class('switch-input') }}
                                    <span class="switch-slider" data-checked="yes" data-unchecked="no"></span>
                                </label>
                            </div><!--col-->
                        </div><!--form-group-->


                        <div class="form-group row">
                            {{ html()->label(__('validation.attributes.backend.access.accounts.override'))->class('col-md-2 form-control-label')->for('override') }}

                            <div class="col-sm-10 col-md-6">
                                <label class="switch switch-label switch-pill switch-primary">
                                    {{ html()->checkbox('override', false)->class('switch-input') }}
                                    <span class="switch-slider" data-checked="yes" data-unchecked="no"></span>
                                </label>
                            </div><!--col-->
                        </div><!--form-group-->


                        <div class="form-group row">
                            {{ html()->label(__('validation.attributes.backend.access.accounts.associated_tags'))->class('col-md-2 form-control-label')->for('tags') }}

                            <div class="col-sm-10 col-md-6">
                                {{ html()->select('tags[]')
                                    ->id('tagSelect')
                                    ->class('form-control')
                                    ->options(getTagsOptions())
                                    ->attribute('multiple', 'multiple')
                                    ->required() }}
                            </div><!--col-->
                        </div><!--form-group-->

                    </div><!--col-->
                </div><!--row-->
            </div><!--card-body-->

            <div class="card-footer clearfix">
                <div class="row">
                    <div class="col">
                        {{ form_cancel(route('admin.accounts.index'), __('buttons.general.cancel')) }}
                    </div><!--col-->

                    <div class="col text-right">
                        {{ form_submit(__('buttons.general.crud.create')) }}
                    </div><!--col-->
                </div><!--row-->
            </div><!--card-footer-->
        </div><!--card-->
    {{ html()->form()->close() }}
@endsection

@push('after-scripts')
<script src="https://unpkg.com/gijgo@1.9.13/js/gijgo.min.js" type="text/javascript"></script>
<!--
<script src="https://res.cloudinary.com/dxfq3iotg/raw/upload/v1569006273/BBBootstrap/choices.min.js?version=7.0.0"></script>
-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/1000hz-bootstrap-validator/0.11.9/validator.min.js"></script>
<script>

{{ getStatesJSON() }}
$(document).ready(function(){

    $('#training_date').datepicker({
        format: 'yyyy-mm-dd',
        uiLibrary: 'bootstrap4'
    });
    jQuery('select#hidden optgroup[label="United States"]').attr('id', 'usChoices');
    jQuery('select#hidden optgroup[label="Canadian Providences"]').attr('id', 'canadianChoices');

    var multipleTags = $("#tagSelect").select2({
        placeholder: 'Select an option',
        maximumSelectionLength: 16,
        allowClear: true,
        tags: true
    });
    var countries = $("#country").select2({
        placeholder: 'Select an country',
        // maximumSelectionLength: 1,
        allowClear: false
    });

    var states = $("#state").select2({
        placeholder: 'Select a state',
        // maximumSelectionLength: 1,
        allowClear: false
    });

    countries.on('change', function(evt, params) {
      var val = $(this).val();
      if ('Canada' == val) {
        jQuery('select#state optgroup[label="United States"]').attr('disabled', 'disabled');
        jQuery('select#state optgroup[label="Canadian Providences"]').attr('disabled', null);
      } else {
        jQuery('select#state optgroup[label="United States"]').attr('disabled', null);
        jQuery('select#state optgroup[label="Canadian Providences"]').attr('disabled', 'disabled');
      }


      $("#state").trigger("change");
      $('#state').trigger('change.select2');
    });
    jQuery('.needs-validation').validator();
});
</script>
@endpush
