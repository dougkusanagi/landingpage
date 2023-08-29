@can('admin')
<li class="nav-item">
	<a class="nav-link" href="{{ route('settings.contacts.index') }}">
		<i class="fas fa-phone-alt"></i>
		<span>@lang('Contacts')</span>
	</a>
</li>
@endcan