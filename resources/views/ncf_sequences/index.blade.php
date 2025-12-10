@extends('layouts.admin')
@section('page-title')
    {{__('NCF Sequences')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('NCF Sequences')}}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        @can('create ncf sequence')
            <a href="#" data-url="{{ route('ncf-sequences.create') }}" data-ajax-popup="true" data-title="{{__('Create NCF Sequence')}}" data-bs-toggle="tooltip" title="{{__('Create')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{__('Type')}}</th>
                                <th>{{__('Serie')}}</th>
                                <th>{{__('Range')}}</th>
                                <th>{{__('Validity')}}</th>
                                <th>{{__('Active')}}</th>
                                <th width="10%">{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($sequences as $sequence)
                                <tr>
                                    <td>{{ optional($sequence->ncfType)->description ?? __('Unknown type') }}</td>
                                    <td>{{ $sequence->serie ?? __('No serie') }}</td>
                                    <td>{{ $sequence->start_number }} - {{ $sequence->end_number }}</td>
                                    <td>
                                        @php
                                            $from = $sequence->valid_from ? \Carbon\Carbon::parse($sequence->valid_from)->format('d/m/Y') : null;
                                            $until = $sequence->valid_until ? \Carbon\Carbon::parse($sequence->valid_until)->format('d/m/Y') : null;
                                        @endphp
                                        {{ $from || $until ? trim(($from ?? '...').' - '.($until ?? '...')) : __('No dates') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $sequence->is_active ? 'success' : 'danger' }} p-2 px-3 rounded">{{ $sequence->is_active ? __('Active') : __('Inactive') }}</span>
                                    </td>
                                    <td class="Action">
                                        <span>
                                            @can('edit ncf sequence')
                                                <div class="action-btn me-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bg-info" data-url="{{ route('ncf-sequences.edit',$sequence->id) }}" data-ajax-popup="true" data-title="{{__('Edit NCF Sequence')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete ncf sequence')
                                                <div class="action-btn">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['ncf-sequences.destroy', $sequence->id],'id'=>'delete-form-'.$sequence->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$sequence->id}}').submit();">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
