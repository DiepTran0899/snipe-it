<!-- Asset Tag Select -->
<div class="form-group{{ $errors->has($fieldname ?? 'asset_tags') ? ' has-error' : '' }}">
    <label for="{{ $fieldname ?? 'asset_tags' }}" class="col-md-3 control-label">{{ $translated_name ?? trans('general.asset_tag') }}</label>
    <div class="col-md-7">
        <select class="js-data-ajax select2" data-endpoint="hardware/taglist" data-placeholder="{{ trans('general.asset_tag') }}" aria-label="{{ $fieldname ?? 'asset_tags' }}" name="{{ ($fieldname ?? 'asset_tags').'[]' }}" id="{{ $select_id ?? 'asset_tags' }}" multiple style="width:100%">
            @if(old($fieldname ?? 'asset_tags'))
                @foreach(old($fieldname ?? 'asset_tags', []) as $tag)
                    <option value="{{ $tag }}" selected="selected">{{ $tag }}</option>
                @endforeach
            @endif
        </select>
    </div>
    {!! $errors->first($fieldname ?? 'asset_tags', '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}
</div>
