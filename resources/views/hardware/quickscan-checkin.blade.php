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

        /* Responsive scanner container */
        #scanner {
            width: 100%;
            max-width: 500px;
            margin: 10px auto;
            background: #f5f5f5;
            border-radius: 4px;
            overflow: hidden;
        }

        #scanner video {
            width: 100%;
            height: auto;
            display: block;
        }

        /* Mobile-first adjustments */
        @media (max-width: 768px) {
            #scanner {
                max-width: 100%;
            }

            .box-body {
                padding: 10px !important;
            }

            #camera-select,
            #start-scanner,
            #stop-scanner {
                width: 100%;
                margin-bottom: 8px;
            }

            #scanner-status {
                font-size: 0.85em;
                padding: 8px;
                background: #f0f0f0;
                border-radius: 3px;
                margin-top: 8px;
            }

            #scanned-list {
                max-height: 250px;
                overflow-y: auto;
            }

            .list-group-item {
                padding: 8px 10px;
                font-size: 0.9em;
            }

            .btn-xs {
                padding: 3px 6px;
                font-size: 0.75em;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            #scanner {
                max-width: 400px;
            }

            #camera-select {
                max-width: 320px;
            }
        }

        @media (min-width: 1025px) {
            #scanner {
                max-width: 500px;
            }

            #camera-select {
                max-width: 350px;
            }
        }

        .scanner-button-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .scanner-button-group .btn {
            flex: 1;
            min-width: 120px;
        }

        @media (max-width: 480px) {
            .scanner-button-group .btn {
                min-width: 100%;
            }
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

                    <!-- Asset Tag -->
                    <div class="form-group {{ $errors->has('asset_tag') ? 'error' : '' }}">
                        <label for="asset_tag" class="col-md-3 control-label" id="checkin_tag">{{ trans('general.asset_tag') }}</label>
                        <div class="col-md-9">
                            <div class="input-group col-md-11 required">
                                <input type="text" class="form-control" name="asset_tag" id="asset_tag" placeholder="{{ trans('general.scan_or_type') }}" value="{{ old('asset_tag') }}" autocomplete="off">

                            </div>
                            {!! $errors->first('asset_tag', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            <div class="help-block">{{ trans('general.quickscan_instructions') }}</div>

                            <!-- Scanner Controls -->
                            <div class="scanner-button-group">
                                <button type="button" id="start-scanner" class="btn btn-primary btn-sm">
                                    <i class="fas fa-camera"></i> {{ trans('general.start_scanner') }}
                                </button>
                                <button type="button" id="stop-scanner" class="btn btn-warning btn-sm" style="display:none">
                                    <i class="fas fa-stop-circle"></i> {{ trans('general.stop_scanner') }}
                                </button>
                            </div>

                            <!-- Camera selector (toggle buttons) -->
                            <div id="scanner-controls" style="margin-top: 8px; display: none;">
                                <label class="control-label" style="display: block; margin-bottom: 8px;">{{ trans('general.select_camera') }}</label>
                                <div id="camera-toggle-group" style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px;"></div>
                                <div id="scanner-status" style="margin-top: 6px; font-size: 0.9em; color: #666;"></div>
                            </div>

                            <!-- QR Scanner Container -->
                            <div id="scanner" style="display: none;"></div>

                            <!-- Scanned Tags List -->
                            <div id="scanned-tags" style="margin-top: 15px; display: none;">
                                <label class="control-label" style="display: block; margin-bottom: 8px;">
                                    <strong>{{ trans('general.scanned_tags') }}</strong>
                                    <span class="badge badge-primary" id="tag-count">0</span>
                                </label>
                                <ul id="scanned-list" class="list-group"></ul>
                            </div>

                        </div>
                    </div>

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
    <script src="/js/html5-qrcode.min.js"></script>
    <script nonce="{{ csrf_token() }}">

        // scannedTags holds the list of scanned asset tags to batch submit
        var scannedTags = [];
        var html5QrcodeScanner = null;

        function addScannedTag(tag) {
            tag = jQuery.trim(tag + '');
            if (!tag) return;
            if (scannedTags.indexOf(tag) !== -1) return;
            scannedTags.push(tag);
            $('#scanned-tags').show();
            $('#tag-count').text(scannedTags.length);
            $('#scanned-list').prepend(
                '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                '<span>' + $('<div/>').text(tag).html() + '</span>' +
                '<button type="button" class="btn btn-xs btn-danger remove-tag" data-tag="' + $('<div/>').text(tag).html() + '">&times;</button>' +
                '</li>'
            );
        }

        $(document).on('click', '.remove-tag', function(){
            var tag = $(this).data('tag');
            scannedTags = scannedTags.filter(function(t){ return t !== tag; });
            $(this).closest('li').remove();
            $('#tag-count').text(scannedTags.length);
            if (scannedTags.length === 0) {
                $('#scanned-tags').hide();
            }
        });

        $('#start-scanner').on('click', function(){
            if (typeof Html5Qrcode === 'undefined') {
                $('#scanner-status').html(
                    '<div style="color: #d9534f; padding: 10px; background: #f2dede; border-radius: 3px;">' +
                    '<i class="fas fa-exclamation-triangle"></i> ' +
                    '{{ trans('general.scanner_library_failed') }}' +
                    '</div>'
                );
                console.error('Html5Qrcode is undefined');
                return;
            }

            $('#scanner').show();
            $('#start-scanner').hide();
            $('#stop-scanner').show();
            $('#scanner-controls').show();

            if (!html5QrcodeScanner) {
                html5QrcodeScanner = new Html5Qrcode("scanner");
                $('#scanner-status').html('<i class="fas fa-spinner fa-spin"></i> {{ trans('general.detecting_cameras') }}');

                if (Html5Qrcode.getCameras) {
                    Html5Qrcode.getCameras().then(cameras => {
                        $('#camera-toggle-group').empty();
                        if (cameras && cameras.length) {
                            cameras.forEach(function(cam, idx){
                                var label = cam.label || ('{{ trans('general.camera') }} ' + (idx+1));
                                var btnClass = (idx === 0) ? 'btn-info active' : 'btn-default';
                                var btn = $('<button type="button" class="btn btn-sm ' + btnClass + ' camera-toggle" data-device-id="' + cam.id + '">')
                                    .html('<i class="fas fa-camera"></i> ' + label);
                                $('#camera-toggle-group').append(btn);
                            });
                            $('#scanner-status').html('<i class="fas fa-check text-success"></i> {{ trans('general.camera_ready') }}');
                            startScannerWithDevice(cameras[0].id);
                        } else {
                            $('#scanner-status').html(
                                '<div style="color: #d9534f;">' +
                                '<i class="fas fa-camera-slash"></i> {{ trans('general.no_camera_found') }}' +
                                '</div>'
                            );
                        }
                    }).catch(err => {
                        console.error('getCameras error', err);
                        $('#scanner-status').html(
                            '<small style="color: #666;">' +
                            '<i class="fas fa-info-circle"></i> {{ trans('general.using_default_camera') }}' +
                            '</small>'
                        );
                        startScannerWithDevice({ facingMode: "environment" });
                    });
                } else {
                    startScannerWithDevice({ facingMode: "environment" });
                }
            } else {
                html5QrcodeScanner.resume().then(() => {
                    $('#scanner-status').html('<i class="fas fa-check text-success"></i> {{ trans('general.scanner_running') }}');
                }).catch(err => {
                    console.error('Resume failed', err);
                    $('#scanner-status').html('<div style="color: #d9534f;"><i class="fas fa-times"></i> ' + err.message + '</div>');
                });
            }
        });

        function startScannerWithDevice(deviceId) {
            if (!html5QrcodeScanner) return;
            const config = { fps: 10, qrbox: { width: 250, height: 250 } };
            html5QrcodeScanner.start(deviceId, config,
                (decodedText, decodedResult) => {
                    addScannedTag(decodedText);
                    html5QrcodeScanner.pause();
                    setTimeout(function(){ html5QrcodeScanner.resume(); }, 700);
                },
                (errorMessage) => {
                    // ignore transient decode errors
                }
            ).then(() => {
                $('#scanner-status').html(
                    '<i class="fas fa-check text-success"></i> ' +
                    '{{ trans('general.scanner_active') }} <small style="display: block; margin-top: 4px; color: #999;">{{ trans('general.point_camera_qr') }}</small>'
                );
            }).catch(err => {
                console.error('Unable to start scanner', err);
                $('#scanner-status').html('<div style="color: #d9534f;"><i class="fas fa-times"></i> ' + err.message + '</div>');
            });
        }

        $('#stop-scanner').on('click', function(){
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().then(() => {
                    $('#scanner').hide();
                    $('#start-scanner').show();
                    $('#stop-scanner').hide();
                    $('#scanner-controls').hide();
                    $('#scanner-status').html('');
                }).catch(err => {
                    console.error('Error stopping scanner', err);
                });
            }
        });

        // Allow manual entry + Enter to add to list
        $('#asset_tag').on('keypress', function(e){
            if (e.which === 13) {
                e.preventDefault();
                var v = $(this).val();
                if (v) {
                    addScannedTag(v);
                    $(this).val('');
                }
            }
        });

        // Camera toggle button handler
        $(document).on('click', '.camera-toggle', function(){
            var deviceId = $(this).data('device-id');
            if (!html5QrcodeScanner) return;
            
            // Update active state
            $('.camera-toggle').removeClass('btn-info active').addClass('btn-default');
            $(this).removeClass('btn-default').addClass('btn-info active');
            
            // Restart scanner with new device
            html5QrcodeScanner.stop().then(function(){
                startScannerWithDevice(deviceId);
            }).catch(function(err){
                console.error('Stop before restart failed', err);
                $('#scanner-status').html('<div style="color: #d9534f;"><i class="fas fa-times"></i> ' + err.message + '</div>');
            });
        });

        $("#checkin-form").submit(function (event) {
            event.preventDefault();
            $('#checkedin-div').show();
            $('#checkin-loader').show();

            // gather common form fields
            var baseFormArray = $('#checkin-form').serializeArray().filter(function(f){ return f.name !== 'asset_tag'; });

            // determine tags to process: scannedTags first, fall back to single input
            var tagsToProcess = scannedTags.slice();
            var single = $('#asset_tag').val();
            if ((tagsToProcess.length === 0) && single) {
                tagsToProcess.push(single);
            }

            if (tagsToProcess.length === 0) {
                // nothing to do
                $('#checkin-loader').hide();
                return false;
            }

            var i = 0;
            function processNext() {
                if (i >= tagsToProcess.length) {
                    $('#checkin-loader').hide();
                    scannedTags = [];
                    $('#scanned-list').empty();
                    $('#scanned-tags').hide();
                    $('#tag-count').text('0');
                    $('#asset_tag').val('');
                    return;
                }

                var tag = tagsToProcess[i];
                var data = baseFormArray.slice();
                data.push({name: 'asset_tag', value: tag});

                $.ajax({
                    url: "{{ route('api.asset.checkinbytag') }}",
                    type : 'POST',
                    headers: {
                        "X-Requested-With": 'XMLHttpRequest',
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType : 'json',
                    data : data,
                    success : function (resp) {
                        if (resp.status == 'success') {
                            $('#checkedin tbody').prepend("<tr class='success'><td>" + resp.payload.asset_tag + "</td><td>" + resp.payload.model + "</td><td>" + resp.payload.model_number + "</td><td>" + resp.messages + "</td><td><i class='fas fa-check text-success'></i></td></tr>");

                            @if ($user?->enable_sounds)
                            var audio = new Audio('{{ config('app.url') }}/sounds/success.mp3');
                            audio.play()
                            @endif

                            incrementOnSuccess();
                        } else {
                            handlecheckinFail(resp, tag);
                        }
                    },
                    error: function (resp) {
                        handlecheckinFail(resp, tag);
                    },
                    complete: function() {
                        i++;
                        // small delay so UI can update smoothly when scanning many tags
                        setTimeout(processNext, 200);
                    }
                });
            }

            processNext();

            return false;
        });

        function handlecheckinFail (data, asset_tag) {

            @if ($user?->enable_sounds)
            var audio = new Audio('{{ config('app.url') }}/sounds/error.mp3');
            audio.play()
            @endif

            if ((!asset_tag) && (data.payload) && (data.payload.asset_tag)) {
                asset_tag = data.payload.asset_tag;
            }

            asset_tag = jQuery('<span>' + asset_tag + '</span>').text();

            var model = '';
            var model_number = '';
            if (data.payload) {
                model = data.payload.model || '';
                model_number = data.payload.model_number || '';
            }

            let messages = "";
            if (data.messages) {
                if (typeof data.messages === 'object') {
                    for (let x in data.messages) {
                        messages += data.messages[x];
                    }
                } else {
                    messages = data.messages;
                }
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
