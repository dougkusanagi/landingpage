@extends('core::layouts.app')
@section('title', __('Blogs'))
@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-2">
  <h1 class="h3 mb-4 text-gray-800">@lang('Blogs')</h1>
  <div class="ml-auto d-sm-flex">
    <form method="get" action="" class="navbar-search mr-4">
      <div class="input-group">
        <input type="text" name="search" value="{{ \Request::get('search', '') }}"
          class="form-control bg-light border-0 small" placeholder="@lang('Search blog')" aria-label="Search"
          aria-describedby="basic-addon2">
        <div class="input-group-append">
          <button class="btn btn-primary" type="submit">
            <i class="fas fa-search fa-sm"></i>
          </button>
        </div>
      </div>
    </form>
    <a href="{{ route('settings.blogs.create') }}" class="btn btn-sm btn-success shadow-sm"><i class="fas fa-plus fa-sm text-white-50"></i> @lang('Create')</a>
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
              <th>@lang('Category')</th>
              <th>@lang('Title')</th>
              <th>@lang('Thumb')</th>
              <th>@lang('Read times')</th>
              <th>@lang('Created at')</th>
              <th></th>
              <th>@lang('Action')</th>
            </tr>
          </thead>
          <tbody>
            @foreach($data as $item)
            <tr>
              <td>
                <small>{{ $item->category->name }}</small>
              </td>
              <td>
                <small>{{ $item->title }}</small>
              </td>
              <td>
                <img src="{{ $item->getThumbLink() }}" alt="{{ $item->title }}" width="50" height="50" />
              </td>
              <td>
                <small>{{ $item->time_read }}</small>
              </td>
              <td>
                <small>{{ $item->created_at }}</small>
              </td>
              <td>
                @if($item->is_active)
                <span class="small text-success"><i class="fas fa-check-circle"></i> @lang('Active')</span>
                @else
                <span class="small text-danger"><i class="fas fa-times-circle"></i> @lang('No Active')</span>
                @endif
                <br>
                @if($item->is_featured)
                <span class="small text-primary"><i class="fas fa-check-circle"></i> @lang('Featured')</span>
                @endif
              </td>
              <td>
                <div class="btn-group">
                  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-h"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-top">
                    <a href="{{ route('settings.blogs.edit', $item) }}" class="dropdown-item">@lang('Edit')</a>
                    <form method="post" action="{{ route('settings.blogs.destroy', $item->id) }}"
                      onsubmit="return confirm('@lang('Confirm delete?')');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="dropdown-item">@lang('Delete')</button>
                    </form>
                  </div>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="mt-4">
      {{ $data->appends( Request::all() )->links() }}
    </div>
    @else
    <div class="text-center">
      <div class="error mx-auto mb-3"><i class="fas fa-briefcase"></i></div>
      <p class="lead text-gray-800">@lang('No blog Found')</p>
      <p class="text-gray-500">@lang("You don't have any blog").</p>
      <a href="{{ route('settings.blogs.create')}}" class="btn btn-primary">
        <span class="text">@lang('New blog')</span>
      </a>
    </div>
    @endif
  </div>
</div>


@stop