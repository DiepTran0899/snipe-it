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
        <form method="GET" action="{{ route('dashboard.custom') }}" class="form-inline" style="margin-bottom:15px">
          <div class="form-group">
            <label for="name">{{ trans('general.name') }}</label>
            <input class="form-control" name="name" id="name" value="{{ request('name') }}">
          </div>
          <div class="form-group" style="margin-left:10px">
            <label for="status_id">{{ trans('general.status') }}</label>
            <select name="status_id" id="status_id" class="form-control">
              <option value="">All</option>
              @foreach(App\Models\Statuslabel::all() as $status)
                <option value="{{ $status->id }}" {{ request('status_id')==$status->id ? 'selected' : '' }}>{{ $status->name }}</option>
              @endforeach
            </select>
          </div>
          <button class="btn btn-primary" type="submit" style="margin-left:10px">Filter</button>
        </form>

        <canvas id="statusChart" height="100"></canvas>

        <table class="table table-bordered" style="margin-top:15px">
          <thead>
            <tr>
              <th>{{ trans('general.name') }}</th>
              <th>{{ trans('general.serial') }}</th>
              <th>{{ trans('general.status') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($assets as $asset)
              <tr>
                <td>{{ $asset->name }}</td>
                <td>{{ $asset->serial }}</td>
                <td>{{ optional($asset->statuslabel)->name }}</td>
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
  var ctx = document.getElementById('statusChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json(array_keys($statusCounts)),
        datasets: [{
            label: 'Assets',
            data: @json(array_values($statusCounts)),
            backgroundColor: 'rgba(60,141,188,0.5)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
  });
</script>
@endpush
