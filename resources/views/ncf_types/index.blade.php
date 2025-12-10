@extends('layouts.admin')
@section('page-title')
    {{__('NCF Types')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('NCF Types')}}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        @can('create ncf type')
            <a href="#" data-url="{{ route('ncf-types.create') }}" data-ajax-popup="true" data-title="{{__('Create NCF Type')}}" data-bs-toggle="tooltip" title="{{__('Create')}}" class="btn btn-sm btn-primary">
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
                                <th>{{__('Code')}}</th>
                                <th>{{__('Description')}}</th>
                                <th>{{__('Active')}}</th>
                                <th width="10%">{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($types as $type)
                                <tr>
                                    <td>{{ $type->code }}</td>
                                    <td>{{ $type->description }}</td>
                                    <td>
                                        <span class="badge bg-{{ $type->is_active ? 'success' : 'danger' }} p-2 px-3 rounded">{{ $type->is_active ? __('Active') : __('Inactive') }}</span>
                                    </td>
                                    <td class="Action">
                                        <span>
                                            @can('edit ncf type')
                                                <div class="action-btn me-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bg-info" data-url="{{ route('ncf-types.edit',$type->id) }}" data-ajax-popup="true" data-title="{{__('Edit NCF Type')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete ncf type')
                                                <div class="action-btn">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['ncf-types.destroy', $type->id],'id'=>'delete-form-'.$type->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$type->id}}').submit();">
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
