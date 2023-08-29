@can('admin')
<li class="nav-item">
	<a class="nav-link" href="{{ route('settings.users.index') }}">
		<i class="fas fa-user"></i>
		<span>@lang('Users')</span>
	</a>
</li>
@endcan