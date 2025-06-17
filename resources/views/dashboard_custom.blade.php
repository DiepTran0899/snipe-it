@extends('layouts/default')

@section('title')
Custom Dashboard
@parent
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title">{{ trans('general.filter') }}</h3>
                <div class="card-tools">
                    <button type="button" id="addFilter" class="btn btn-sm btn-secondary mr-2">
                        <x-icon type="plus" /> {{ trans('button.add') }}
                    </button>
                    <button id="applyFilters" class="btn btn-sm btn-primary" type="submit" form="filterForm">
                        <x-icon type="filter" /> {{ trans('button.filter') }}
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="filterForm">
                    <div id="filterRows">
                        <div class="form-row filter-row mb-2 align-items-center">
                            <div class="col-md-3">
                                <select class="form-control filter-field">
                                    <option value="name">{{ trans('general.name') }}</option>
                                    <option value="serial">Serial</option>
                                    <option value="status">{{ trans('general.status') }}</option>
                                    <option value="model">{{ trans('general.model') }}</option>
                                    <option value="location">{{ trans('general.location') }}</option>
                                    <option value="department">{{ trans('general.department') }}</option>
                                    <option value="user">{{ trans('general.user') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4 filter-value-wrapper">
                                <input type="text" class="form-control filter-value" />
                            </div>
                            <div class="col-md-1 text-right">
                                <button type="button" class="btn btn-sm btn-danger removeFilter">
                                    <x-icon type="times" />
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3" id="widgetContainer">
    <div class="col-lg-6 widget">
        <div class="card card-primary">
            <div class="card-header ui-sortable-handle">
                <h3 class="card-title">{{ trans('general.assets_by_status') }}</h3>
                <div class="card-tools">
                    <select id="chart_type" name="chart_type" class="custom-select custom-select-sm">
                        <option value="bar">Bar</option>
                        <option value="pie">Pie</option>
                        <option value="doughnut">Doughnut</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 widget">
        <div class="card card-default">
            <div class="card-header ui-sortable-handle">
                <h3 class="card-title">{{ trans('general.assets') }}</h3>
            </div>
            <div class="card-body">
                <table id="assetsTable" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('general.name') }}</th>
                            <th>{{ trans('general.serial') }}</th>
                            <th>{{ trans('general.status') }}</th>
                            <th>{{ trans('general.model') }}</th>
                            <th>{{ trans('general.location') }}</th>
                            <th>{{ trans('general.department') }}</th>
                            <th>{{ trans('general.user') }}</th>
                            <th>{{ trans('general.updated_at') }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<style nonce="{{ csrf_token() }}">
    .ui-sortable-handle { cursor: move; }
</style>
@endpush

@push('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script nonce="{{ csrf_token() }}">
    var assets = [];
    var table = null;
    var statusChart = null;
    var lists = {
        statuses: @json($statuses),
        models: @json($models),
        locations: @json($locations),
        departments: @json($departments ?? []),
        users: @json($users)
    };

    function buildValueInput(field, wrapper){
        var selectFields = ['status','model','location','department','user'];
        wrapper.empty();
        if(selectFields.indexOf(field) !== -1){
            var mapping = {status:'statuses',model:'models',location:'locations',department:'departments',user:'users'};
            var sel = $('<select class="form-control filter-value"></select>');
            sel.append('<option value="">{{ trans('general.all') }}</option>');
            lists[mapping[field]].forEach(function(item){
                sel.append('<option value="'+item.name+'">'+item.name+'</option>');
            });
            wrapper.append(sel);
        }else{
            wrapper.append('<input type="text" class="form-control filter-value"/>');
        }
    }

    function addFilterRow(){
        var row = $('#filterRows .filter-row:first').clone();
        row.find('input, select').val('');
        $('#filterRows').append(row);
    }

    function applyFilters(){
        var filtered = assets.filter(function(a){
            var ok = true;
            $('#filterRows .filter-row').each(function(){
                var field = $(this).find('.filter-field').val();
                var value = $(this).find('.filter-value').val();
                if(!value) return;
                if(field === 'name' || field === 'serial'){
                    if(String(a[field]).toLowerCase().indexOf(value.toLowerCase()) === -1) ok = false;
                }else{
                    if(String(a[field]) !== value) ok = false;
                }
            });
            return ok;
        });
        table.clear().rows.add(filtered).draw();
        drawChart(filtered);
    }

    function drawChart(data){
        var counts = {};
        data.forEach(function(a){
            var s = a.status || 'Unknown';
            counts[s] = (counts[s]||0)+1;
        });
        var chartData = {
            labels: Object.keys(counts),
            datasets: [{data:Object.values(counts),backgroundColor:'rgba(60,141,188,0.5)'}]
        };
        if(statusChart){statusChart.destroy();}
        statusChart = new Chart(document.getElementById('statusChart'), {
            type: $('#chart_type').val(),
            data: chartData,
            options:{responsive:true, maintainAspectRatio:false}
        });
    }

    $(function(){
        $('#widgetContainer').sortable({ handle: '.ui-sortable-handle', placeholder: 'sort-highlight' });

        $('#filterRows').on('change', '.filter-field', function(){
            buildValueInput($(this).val(), $(this).closest('.filter-row').find('.filter-value-wrapper'));
        });

        $('#filterRows').on('click', '.removeFilter', function(){
            if($('#filterRows .filter-row').length > 1){ $(this).closest('.filter-row').remove(); }
        });

        $('#addFilter').on('click', addFilterRow);

        $('#filterForm').on('submit', function(e){ e.preventDefault(); applyFilters(); });

        $.getJSON('{{ route('dashboard.custom.data') }}', function(data){
            assets = data;
            table = $('#assetsTable').DataTable({ data: assets, columns:[
                {data:'name'},
                {data:'serial'},
                {data:'status'},
                {data:'model'},
                {data:'location'},
                {data:'department'},
                {data:'user'},
                {data:'updated_at'}
            ]});
            applyFilters();
        });

        $('#chart_type').on('change', function(){ applyFilters(); });
    });
</script>
@endpush
