@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.quickscan_checkin') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <style>

        .input-group {
            padding-left: 0px !important;
        }
    </style>



    <div class="row">
    <form method="POST" action="{{ route('hardware/quickscancheckin') }}" accept-charset="UTF-8" class="form-horizontal" role="form" id="checkin-form">
        <!-- left column -->
        <div class="col-md-6">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title"> {{ trans('admin/hardware/general.bulk_checkin') }} </h2>
                </div>
                <div class="box-body">
                    {{csrf_field()}}

                    @include ('partials.forms.edit.asset-select', [
                        'translated_name' => trans('general.asset_tag'),
                        'fieldname' => 'asset_ids[]',
                        'select_id' => 'asset_tag',
                        'multiple' => true
                    ])

                    <!-- Status -->
                    <div class="form-group {{ $errors->has('status_id') ? 'error' : '' }}">
                        <label for="status_id" class="col-md-3 control-label">
                            {{ trans('admin/hardware/form.status') }}
                        </label>
                        <div class="col-md-7">
                            <x-input.select
                                name="status_id"
                                id="status_id"
                                :options="$statusLabel_list"
                                style="width:100%"
                                aria-label="status_id"
                            />
                            {!! $errors->first('status_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                        </div>
                    </div>

                    <!-- Locations -->
                    @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'location_id'])

                    <!-- Note -->
                        <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
                            <label for="note" class="col-md-3 control-label">{{ trans('admin/hardware/form.notes') }}</label>
                            <div class="col-md-8">
                                <textarea class="col-md-6 form-control" id="note" name="note">{{ old('note') }}</textarea>
                                {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>



                </div> <!--/.box-body-->
                <div class="box-footer">
                    <a class="btn btn-link" href="{{ route('hardware.index') }}"> {{ trans('button.cancel') }}</a>
                    <button type="submit" id="checkin_button" class="btn btn-success pull-right"><x-icon type="checkmark" /> {{ trans('general.checkin') }}</button>
                </div>



            </div>



            </form>
        </div> <!--/.col-md-6-->

        <div class="col-md-6">
            <div class="box box-default" id="checkedin-div" style="display: none">
                <div class="box-header with-border">
                    <h2 class="box-title"> {{ trans('general.quickscan_checkin_status') }} (<span id="checkin-counter">0</span> {{ trans('general.assets_checked_in_count') }}) </h2>
                </div>
                <div class="box-body">

                    <table id="checkedin" class="table table-striped snipe-table">
                        <thead>
                        <tr>
                            <th>{{ trans('general.asset_tag') }}</th>
                            <th>{{ trans('general.asset_model') }}</th>
                            <th>{{ trans('general.model_no') }}</th>
                            <th>{{ trans('general.quickscan_checkin_status') }}</th>
                            <th></th>
                        </tr>
                        <tr id="checkin-loader" style="display: none;">
                            <td colspan="3">
                                <x-icon type="spinner" />  {{ trans('general.processing') }}...
                            </td>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>


@stop


@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        var baseUrl = $('meta[name="baseUrl"]').attr('content');

        $("#checkin-form").submit(function (event) {
            $('#checkedin-div').show();
            $('#checkin-loader').show();

            event.preventDefault();

            var assetIds = $('#asset_tag').val() || [];
            var formArray = $('#checkin-form').serializeArray();
            var data = {};
            $.each(formArray, function(i, field){
                if (field.name !== 'asset_ids[]') {
                    data[field.name] = field.value;
                }
            });

            function checkinNext(index) {
                if (index >= assetIds.length) {
                    $('#checkin-loader').hide();
                    $('#asset_tag').val(null).trigger('change');
                    return;
                }

                $.ajax({
                    url: baseUrl + 'api/v1/hardware/' + assetIds[index] + '/checkin',
                    type : 'POST',
                    headers: {
                        "X-Requested-With": 'XMLHttpRequest',
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType : 'json',
                    data : data,
                    success : function (response) {
                        if (response.status == 'success') {
                            $('#checkedin tbody').prepend("<tr class='success'><td>" + response.payload.asset_tag + "</td><td>" + response.payload.model + "</td><td>" + response.payload.model_number + "</td><td>" + response.messages + "</td><td><i class='fas fa-check text-success'></i></td></tr>");
                            @if ($user?->enable_sounds)
                            var audio = new Audio('{{ config('app.url') }}/sounds/success.mp3');
                            audio.play()
                            @endif
                            incrementOnSuccess();
                        } else {
                            handlecheckinFail(response);
                        }
                    },
                    error: function (response) {
                        handlecheckinFail(response);
                    },
                    complete: function() {
                        checkinNext(index + 1);
                    }

                });
            }

            checkinNext(0);

            return false;
        });

        function handlecheckinFail (data) {

            @if ($user?->enable_sounds)
            var audio = new Audio('{{ config('app.url') }}/sounds/error.mp3');
            audio.play()
            @endif

            if (data.payload.asset_tag) {
                var asset_tag = data.payload.asset_tag;
                var model = data.payload.model;
                var model_number = data.payload.model_number;
            } else {
                var asset_tag = '';
                var model = '';
                var model_number = '';
            }
            if (data.messages) {
                var messages = data.messages;
            } else {
                var messages = '';
            }
            $('#checkedin tbody').prepend("<tr class='danger'><td>" + asset_tag + "</td><td>" + model + "</td><td>" + model_number + "</td><td>" + messages + "</td><td><i class='fas fa-times text-danger'></i></td></tr>");
        }

        function incrementOnSuccess() {
            var x = parseInt($('#checkin-counter').html());
            y = x + 1;
            $('#checkin-counter').html(y);
        }

        $("#checkin_tag").focus();

    </script>
@stop
