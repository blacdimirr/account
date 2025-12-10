{{ Form::model($ncf_type, ['route' => ['ncf-types.update', $ncf_type->id], 'method' => 'PUT','class'=>'needs-validation','novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('code', __('Type Code'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::text('code', null, ['class' => 'form-control','required'=>'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::text('description', null, ['class' => 'form-control','required'=>'required']) }}
        </div>
        <div class="form-group col-md-12 mt-2">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" {{ $ncf_type->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">{{__('Active')}}</label>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}
