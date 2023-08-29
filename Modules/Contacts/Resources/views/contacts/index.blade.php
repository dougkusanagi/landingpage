@extends('core::layouts.app')
@section('title', __('Contacts'))
@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-2">
  <h1 class="h3 mb-4 text-gray-800">@lang('Contacts')</h1>
  <div class="ml-auto d-sm-flex">
    <form method="get" action="" class="navbar-search mr-4">
        <div class="input-group">
            <input type="text" name="search" value="{{ \Request::get('search', '') }}" class="form-control bg-light border-0 small" placeholder="@lang('Search contact')" aria-label="Search" aria-describedby="basic-addon2">
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </div>
    </form>
  </div>
</div>

<div class="row">
  <div class="col-md-3">
    @include('core::partials.settings-sidebar')
  </div>
  <div class="col-md-9">
        @if($data->count() > 0)
            <div class="card">
                <div class="table-responsive">
                     <table class="table card-table table-vcenter text-nowrap">
                        <thead class="thead-dark">
                            <tr>
                                <th>@lang('Fullname')</th>
                                <th>@lang('Info')</th>
                                <th>@lang('Subject')</th>
                                <th>@lang('Created at')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>            
                            @foreach($data as $item)
                            <tr>
                                <td>
                                    <small>
                                      @if($item->is_readed)
                                        {{ $item->fullname }}
                                      @else 
                                        <strong>{{ $item->fullname }}</strong>
                                      @endif
                                    </small>
                                </td>
                                <td>
                                  <small><label>@lang('Phone')</label> <a href="tel:{{ $item->phone }}">{{ $item->phone }}</a></small><br />
                                  <small><label>@lang('Email')</label> <a href="mailto:{{ $item->email }}">{{ $item->email }}</a></small>
                                </td>
                                <td>
                                    <small>{{ $item->subject }}</small><br />
                                    <small><a href="javascript:void(0);" class="btn-show-content" data-id="{{ $item->id }}" data-subject="{{ $item->subject }}" data-content="{{ $item->content }}" data-readed="{{ $item->is_readed ? '1' : '0' }}">@lang('View content...')</a></small>
                                </td>
                                <td>
                                  <small>{{ $item->created_at }}</small>
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <form method="post" action="{{ route('settings.contacts.destroy', $item->id) }}" onsubmit="return confirm('@lang('Confirm delete?')');">
                                      @csrf
                                      @method('DELETE')
                                        <button type="submit" class="btn btn-danger">@lang('Delete')</button>
                                    </form>
                                  </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        @if($data->count() == 0)
        <div class="text-center">
          <div class="error mx-auto mb-3"><i class="fas fa-briefcase"></i></div>
          <p class="lead text-gray-800">@lang('No contact Found')</p>
          <p class="text-gray-500">@lang("You don't have any contact").</p>
        </div>
        @endif
        <div class="mt-4">
            {{ $data->appends( Request::all() )->links() }}
        </div>
    </div>
</div>



<div id="modalContent" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
      </div>
    </div>
  </div>
</div>

@stop

@push('scripts')
  <script>
    const URL_POST_READED_AJAX = "{{ route('settings.contacts.ajaxreaded') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";
  </script>
  <script src="{{ Module::asset('contacts:js/contacts.js') }}"></script>
@endpush