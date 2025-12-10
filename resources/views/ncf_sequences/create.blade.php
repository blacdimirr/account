{{ Form::open(['url' => 'ncf-sequences','class'=>'needs-validation','novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('ncf_type_id', __('NCF Type'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::select('ncf_type_id', $types, null, ['class' => 'form-control select2','required'=>'required','placeholder'=>__('Select NCF type')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('serie', __('Serie/Prefix'),['class'=>'form-label']) }}
            {{ Form::text('serie', '', ['class' => 'form-control','placeholder'=>__('Optional series')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('start_number', __('Start Number'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::number('start_number', '', ['class' => 'form-control','required'=>'required','min'=>1]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('end_number', __('End Number'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::number('end_number', '', ['class' => 'form-control','required'=>'required','min'=>1]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('current_number', __('Current Number'),['class'=>'form-label']) }}
            {{ Form::number('current_number', '', ['class' => 'form-control','min'=>0,'placeholder'=>__('Leave empty to start from beginning')]) }}
        </div>
        <div class="form-group col-md-6"></div>
        <div class="form-group col-md-6">
            {{ Form::label('valid_from', __('Valid From'),['class'=>'form-label']) }}
            {{ Form::date('valid_from', '', ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('valid_until', __('Valid Until'),['class'=>'form-label']) }}
            {{ Form::date('valid_until', '', ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-12 mt-2">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" checked>
                <label class="form-check-label" for="is_active">{{__('Active')}}</label>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}
