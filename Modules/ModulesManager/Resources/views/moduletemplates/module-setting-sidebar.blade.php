@can('admin')
<li class="nav-item">
	<a class="nav-link" href="{{ route('settings.modulesmanager.index') }}">
		<i class="fas fa-box"></i>
		<span>@lang('Modules Manager')</span>
	</a>
</li>
@endcan