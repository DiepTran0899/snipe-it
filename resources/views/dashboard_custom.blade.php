@extends('layouts/default')

@section('title')
Custom Dashboard
@parent
@stop

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-header with-border">
        <h2 class="box-title">Custom Dashboard</h2>
      </div>
      <div class="box-body">
        <form method="GET" action="{{ route('dashboard.custom') }}" class="form-inline mb-3">
          <div class="form-group mr-2">
            <label for="search" class="mr-1">{{ trans('general.search') }}</label>
            <input class="form-control" name="search" id="search" value="{{ request('search') }}">
          </div>
          <div class="form-group mr-2">
            <label for="status_id" class="mr-1">{{ trans('general.status') }}</label>
            <select name="status_id" id="status_id" class="form-control">
              <option value="">{{ trans('general.all') }}</option>
              @foreach($statuses as $id => $name)
                <option value="{{ $id }}" {{ request('status_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group mr-2">
            <label for="model_id" class="mr-1">{{ trans('general.model') }}</label>
            <select name="model_id" id="model_id" class="form-control">
              <option value="">{{ trans('general.all') }}</option>
              @foreach($models as $id => $name)
                <option value="{{ $id }}" {{ request('model_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group mr-2">
            <label for="location_id" class="mr-1">{{ trans('general.location') }}</label>
            <select name="location_id" id="location_id" class="form-control">
              <option value="">{{ trans('general.all') }}</option>
              @foreach($locations as $id => $name)
                <option value="{{ $id }}" {{ request('location_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group mr-2">
            <label for="user_id" class="mr-1">{{ trans('general.user') }}</label>
            <select name="user_id" id="user_id" class="form-control">
              <option value="">{{ trans('general.all') }}</option>
              @foreach($users as $id => $name)
                <option value="{{ $id }}" {{ request('user_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group mr-2">
            <label for="chart_type" class="mr-1">Chart</label>
            <select id="chart_type" name="chart_type" class="form-control">
              <option value="bar" {{ request('chart_type', 'bar') == 'bar' ? 'selected' : '' }}>Bar</option>
              <option value="pie" {{ request('chart_type') == 'pie' ? 'selected' : '' }}>Pie</option>
            </select>
          </div>
          <button class="btn btn-primary" type="submit">Filter</button>
        </form>

        <div class="mb-3">
          <canvas id="statusChart" height="120"></canvas>
        </div>

        <table id="assetsTable" class="table table-bordered table-hover snipe-table" data-search="true" data-show-refresh="true" data-show-columns="true" data-page-size="10">
          <thead>
            <tr>
              <th>{{ trans('general.name') }}</th>
              <th>{{ trans('general.serial') }}</th>
              <th>{{ trans('general.status') }}</th>
              <th>{{ trans('general.model') }}</th>
              <th>{{ trans('general.location') }}</th>
              <th>{{ trans('general.user') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($assets as $asset)
              <tr>
                <td>{{ $asset->name }}</td>
                <td>{{ $asset->serial }}</td>
                <td>{{ optional($asset->assetstatus)->name }}</td>
                <td>{{ optional($asset->model)->name }}</td>
                <td>{{ optional($asset->location)->name }}</td>
                <td>{{ optional($asset->assignedTo)->name ?? optional($asset->assignedTo)->first_name }}</td>
              </tr>
            @empty
              <tr><td colspan="3">No assets found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@stop

@push('js')
<script nonce="{{ csrf_token() }}">
  var chartData = {
      labels: @json(array_keys($statusCounts)),
      datasets: [{
          label: 'Assets',
          data: @json(array_values($statusCounts)),
          backgroundColor: 'rgba(60,141,188,0.5)'
      }]
  };
  var ctx = document.getElementById('statusChart').getContext('2d');
  var currentType = '{{ request('chart_type', 'bar') }}';
  var chart = new Chart(ctx, { type: currentType, data: chartData, options: { responsive:true, maintainAspectRatio:false } });

  document.getElementById('chart_type').addEventListener('change', function() {
      chart.destroy();
      chart = new Chart(ctx, { type: this.value, data: chartData, options: { responsive:true, maintainAspectRatio:false } });
  });
</script>
@endpush

@include('partials.bootstrap-table')
