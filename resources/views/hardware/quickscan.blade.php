@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.bulkaudit') }}
    @parent
@stop

{{-- CSS section --}}
@push('moar_css')
    <link rel="stylesheet" href="{{ asset('css/scanner.css') }}">
@endpush

{{-- Page content --}}
@section('content')



    <div class="row">
    <form method="POST" accept-charset="UTF-8" class="form-horizontal" role="form" id="audit-form">
        <!-- left column -->
        <div class="col-md-6">
            <div class="box box-default">

                    <div class="box-body">
                    {{csrf_field()}}

                    <!-- Next Audit -->
                        <div class="form-group {{ $errors->has('asset_tag') ? 'error' : '' }}">
                            <label for="asset_tag" class="col-md-3 control-label" id="audit_tag">{{ trans('general.asset_tag') }}</label>
                            <div class="col-md-9">
                                <div class="input-group date col-md-11 required" data-date-format="yyyy-mm-dd">
                                    <input type="text" class="form-control" name="asset_tag" id="asset_tag" placeholder="{{ trans('general.scan_or_type') }}" value="{{ old('asset_tag') }}" autocomplete="off">
                                    <span class="input-group-btn">
                                        <button type="button" id="add-tag-btn" class="btn btn-primary" title="Add to list">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </span>
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
                                    
                                    <!-- Zoom Controls -->
                                    <div id="zoom-controls" style="margin-top: 12px; display: flex; align-items: center; gap: 8px;">
                                        <button type="button" id="zoom-out-btn" class="btn btn-default btn-sm">
                                            <i class="fas fa-search-minus"></i>
                                        </button>
                                        <span id="zoom-level-display" style="min-width: 40px; text-align: center; font-weight: 600; color: #333;">1.0x</span>
                                        <button type="button" id="zoom-in-btn" class="btn btn-default btn-sm">
                                            <i class="fas fa-search-plus"></i>
                                        </button>
                                    </div>
                                    
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



                        <!-- Locations -->
                    @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'location_id'])


                    <!-- Update location -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-md-9">
                                <label class="form-control">
                                    <input type="checkbox" value="1" name="update_location" {{ old('update_location') == '1' ? ' checked="checked"' : '' }}>
                                    <span>{{ trans('admin/hardware/form.asset_location') }}
                                    <a href="#" class="text-dark-gray" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="<i class='far fa-life-ring'></i> {{ trans('general.more_info') }}" data-html="true" data-content="{{ trans('general.quickscan_bulk_help') }}">
                                        <x-icon type="more-info" /></a></span>
                                </label>
                            </div>
                        </div>


                        <!-- Next Audit -->
                        <div class="form-group {{ $errors->has('next_audit_date') ? 'error' : '' }}">
                            <label for="next_audit_date" class="col-md-3 control-label">{{ trans('general.next_audit_date') }}</label>
                            <div class="col-md-9">
                                <div class="input-group date col-md-5" data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-clear-btn="true">
                                    <input type="text" class="form-control" placeholder="{{ trans('general.next_audit_date') }}" name="next_audit_date" id="next_audit_date" value="{{ old('next_audit_date', $next_audit_date) }}">
                                    <span class="input-group-addon"><x-icon type="calendar" /></span>
                                </div>
                                {!! $errors->first('next_audit_date', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>


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
                        <button type="submit" id="audit_button" class="btn btn-success pull-right">
                            <x-icon type="checkmark" />
                            {{ trans('general.audit') }}
                        </button>
                    </div>
            </div>



            </form>
        </div> <!--/.col-md-6-->

        <div class="col-md-6">
            <div class="box box-default" id="audited-div" style="display: none">
                <div class="box-header with-border">
                    <h2 class="box-title"> {{ trans('general.bulkaudit_status') }} (<span id="audit-counter">0</span> {{ trans('general.assets_audited') }}) </h2>
                </div>
                <div class="box-body">

                    <table id="audited" class="table table-striped snipe-table">
                        <thead>
                        <tr>
                            <th>{{ trans('general.asset_tag') }}</th>
                            <th>{{ trans('general.bulkaudit_status') }}</th>
                            <th></th>
                        </tr>
                        <tr id="audit-loader" style="display: none;">
                            <td colspan="3">
                                <x-icon type="spinner" />
                                {{ trans('admin/hardware/form.processing') }}
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
    <script src="/js/qrcode-scanner-v3.js"></script>
    <script nonce="{{ csrf_token() }}">

        // scannedTags holds the list of scanned asset tags to batch submit
        var scannedTags = [];
        var scannedTagsWithType = [];

        function addScannedTag(tag, source = 'manual') {
            tag = jQuery.trim(tag + '');
            if (!tag) return;
            
            var lookupType = 'tag'; // default: search by asset_tag
            var displaySource = '';
            
            // Extract from QR code URLs
            // /hardware/212 → ID 212 (search by ID first)
            // /ht/24 → Asset Tag 24 (search by asset_tag first)
            if (tag.includes('http://') || tag.includes('https://')) {
                var hardwareMatch = tag.match(/\/hardware\/(\d+)(?:\/|$)/);
                var htMatch = tag.match(/\/ht\/(\w+)(?:\/|$)/);
                
                if (hardwareMatch && hardwareMatch[1]) {
                    tag = hardwareMatch[1];
                    lookupType = 'id';
                    displaySource = ' (ID)';
                    source = 'qr_url';
                } else if (htMatch && htMatch[1]) {
                    tag = htMatch[1];
                    lookupType = 'tag';
                    displaySource = ' (Tag)';
                    source = 'qr_url';
                }
            }
            
            // Check if already scanned
            var duplicate = scannedTagsWithType.find(function(item) { return item.tag === tag && item.lookupType === lookupType; });
            if (duplicate) return;
            
            scannedTagsWithType.push({ tag: tag, lookupType: lookupType, source: source });
            scannedTags.push(tag);
            $('#scanned-tags').show();
            $('#tag-count').text(scannedTagsWithType.length);
            
            var badgeClass = (lookupType === 'id') ? 'badge-warning' : 'badge-info';
            var badgeText = (lookupType === 'id') ? '{{ trans("general.numeric_id") }}' : '{{ trans("general.asset_tag") }}';
            
            // Use index for reliable removal
            var currentIndex = scannedTagsWithType.length - 1;
            
            $('#scanned-list').prepend(
                '<li class="list-group-item d-flex justify-content-between align-items-center" data-index="' + currentIndex + '">' +
                '<span>' + $('<div/>').text(tag).html() + ' <span class="badge ' + badgeClass + '" style="margin-left:8px;">' + badgeText + '</span></span>' +
                '<button type="button" class="btn btn-xs btn-danger remove-tag" data-index="' + currentIndex + '">&times;</button>' +
                '</li>'
            );
        }

        $(document).on('click', '.remove-tag', function(){
            var index = parseInt($(this).data('index'));
            if (index >= 0 && index < scannedTagsWithType.length) {
                scannedTagsWithType.splice(index, 1);
                scannedTags.splice(index, 1);
            }
            $(this).closest('li').remove();
            
            // Reindex all remaining items
            $('#scanned-list li').each(function(i){
                var actualIndex = scannedTagsWithType.length - 1 - i;
                $(this).attr('data-index', actualIndex);
                $(this).find('.remove-tag').attr('data-index', actualIndex);
            });
            
            $('#tag-count').text(scannedTagsWithType.length);
            if (scannedTagsWithType.length === 0) {
                $('#scanned-tags').hide();
            }
        });

        $('#start-scanner').on('click', function(){
            if (typeof Html5Qrcode === 'undefined' || typeof QRCodeScanner === 'undefined') {
                $('#scanner-status').html(
                    '<div style="color: #d9534f; padding: 10px; background: #f2dede; border-radius: 3px;">' +
                    '<i class="fas fa-exclamation-triangle"></i> ' +
                    '{{ trans('general.scanner_library_failed') }}' +
                    '</div>'
                );
                console.error('Required libraries not loaded');
                return;
            }

            $('#scanner').show();
            $('#start-scanner').hide();
            $('#stop-scanner').show();
            $('#scanner-controls').show();

            // Initialize scanner on first use
            if (!QRCodeScanner.getScanner()) {
                QRCodeScanner.init('scanner');
            }

            $('#scanner-status').html('<i class="fas fa-spinner fa-spin"></i> {{ trans('general.detecting_cameras') }}');

            QRCodeScanner.getCameras()
                .then(cameras => {
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
                })
                .catch(err => {
                    console.error('getCameras error', err);
                    $('#scanner-status').html(
                        '<small style="color: #666;">' +
                        '<i class="fas fa-info-circle"></i> {{ trans('general.using_default_camera') }}' +
                        '</small>'
                    );
                    startScannerWithDevice({ facingMode: "environment" });
                });
        });

        function startScannerWithDevice(deviceId) {
            QRCodeScanner.startScannerWithDevice(
                deviceId,
                function(decodedText) {
                    // Success callback
                    addScannedTag(decodedText);
                },
                function(error) {
                    // Error callback
                    console.error('Scanner error:', error);
                    $('#scanner-status').html('<div style="color: #d9534f;"><i class="fas fa-times"></i> ' + error.message + '</div>');
                }
            );
        }

        $('#stop-scanner').on('click', function(){
            QRCodeScanner.stop().then(() => {
                $('#scanner').hide();
                $('#start-scanner').show();
                $('#stop-scanner').hide();
                $('#scanner-controls').hide();
                $('#scanner-status').html('');
            }).catch(err => {
                console.error('Error stopping scanner', err);
            });
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

        // Add tag button click handler
        $('#add-tag-btn').on('click', function(){
            var v = $('#asset_tag').val();
            if (v) {
                addScannedTag(v);
                $('#asset_tag').val('').focus();
            }
        });

        $("#audit-form").submit(function (event) {
            event.preventDefault();
            $('#audited-div').show();
            $('#audit-loader').show();

            // gather common form fields
            var baseFormArray = $('#audit-form').serializeArray().filter(function(f){ return f.name !== 'asset_tag'; });

            // determine tags to process: scannedTagsWithType first, fall back to single input
            var tagsToProcess = scannedTagsWithType.slice();
            var single = $('#asset_tag').val();
            if ((tagsToProcess.length === 0) && single) {
                // Manual entry defaults to tag lookup
                tagsToProcess.push({ tag: single, lookupType: 'tag', source: 'manual' });
            }

            if (tagsToProcess.length === 0) {
                // nothing to do
                $('#audit-loader').hide();
                return false;
            }

            var i = 0;
            function processNext() {
                if (i >= tagsToProcess.length) {
                    $('#audit-loader').hide();
                    scannedTagsWithType = [];
                    scannedTags = [];
                    $('#scanned-list').empty();
                    $('#scanned-tags').hide();
                    $('#tag-count').text('0');
                    $('#asset_tag').val('');
                    return;
                }

                var tagData = tagsToProcess[i];
                var data = baseFormArray.slice();
                data.push({name: 'asset_tag', value: tagData.tag});
                data.push({name: 'lookup_type', value: tagData.lookupType}); // Send lookup type

                $.ajax({
                    url: "{{ route('api.asset.audit.legacy') }}",
                    type : 'POST',
                    headers: {
                        "X-Requested-With": 'XMLHttpRequest',
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType : 'json',
                    data : data,
                    success : function (resp) {
                        if (resp.status == 'success') {
                            $('#audited tbody').prepend("<tr class='success'><td>" + resp.payload.asset_tag + "</td><td>" + resp.messages + "</td><td><i class='fas fa-check text-success' style='font-size:18px;'></i></td></tr>");
                            @if ($user->enable_sounds)
                            var audio = new Audio('{{ config('app.url') }}/sounds/success.mp3');
                            audio.play()
                            @endif
                            incrementOnSuccess();
                        } else {
                            handleAuditFail(resp, tagData.tag);
                        }
                    },
                    error: function (resp) {
                        handleAuditFail(resp, tagData.tag);
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

        // Camera toggle button handler
        $(document).on('click', '.camera-toggle', function(){
            var deviceId = $(this).data('device-id');
            
            // Update active state
            $('.camera-toggle').removeClass('btn-info active').addClass('btn-default');
            $(this).removeClass('btn-default').addClass('btn-info active');
            
            // Restart scanner with new device
            QRCodeScanner.stop().then(function(){
                startScannerWithDevice(deviceId);
            }).catch(function(err){
                console.error('Stop before restart failed', err);
                $('#scanner-status').html('<div style="color: #d9534f;"><i class="fas fa-times"></i> ' + err.message + '</div>');
            });
        });

        // Zoom control handlers
        $('#zoom-in-btn').on('click', function(){
            QRCodeScanner.zoomIn();
        });

        $('#zoom-out-btn').on('click', function(){
            QRCodeScanner.zoomOut();
        });
        function handleAuditFail (data, asset_tag) {
            @if ($user->enable_sounds)
            var audio = new Audio('{{ config('app.url') }}/sounds/error.mp3');
            audio.play()
            @endif


            if ((!asset_tag) && (data.payload)  && (data.payload.asset_tag)) {
                asset_tag = data.payload.asset_tag;
            }

            asset_tag = jQuery('<span>' + asset_tag + '</span>').text();

            let messages = "";

            // Loop through the error messages
            if ((data.messages)  && (data.messages)) {
                for (let x in data.messages) {
                    messages += data.messages[x];
                }
            }

            $('#audited tbody').prepend("<tr class='danger'><td>" + asset_tag + "</td><td>" + messages + "</td><td><i class='fas fa-times text-danger' style='font-size:18px;'></i></td></tr>");
        }

        function incrementOnSuccess() {
            var x = parseInt($('#audit-counter').html());
            y = x + 1;
            $('#audit-counter').html(y);
        }

        $("#audit_tag").focus();

    </script>
@stop
