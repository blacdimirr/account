@extends('layouts.admin')

@section('page-title')
    {{ __('DGII Formats') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Report') }}</li>
    <li class="breadcrumb-item">{{ __('DGII') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => 'report.dgii.export', 'method' => 'POST']) }}
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('format', __('Formato'), ['class' => 'form-label']) }}
                                {{ Form::select('format', ['606' => '606 Compras/Gastos', '607' => '607 Ventas', '608' => '608 Notas de Crédito'], '606', ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('month', __('Mes'), ['class' => 'form-label']) }}
                                {{ Form::select('month', $months, date('n'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('year', __('Año'), ['class' => 'form-label']) }}
                                {{ Form::select('year', $years, date('Y'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            <div class="form-group">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary"><i class="ti ti-download"></i> {{ __('Descargar') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
