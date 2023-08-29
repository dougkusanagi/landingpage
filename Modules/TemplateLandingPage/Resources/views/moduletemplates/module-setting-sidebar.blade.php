@can('admin')
<li class="nav-item">
	<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSettingLandingPage" aria-expanded="false" aria-controls="collapseSettingLandingPage">
		<i class="fas fa-paper-plane"></i>
		<span>@lang('Landing Pages')</span>
	</a>
	<div id="collapseSettingLandingPage" class="collapse {{ (request()->is('settings/templates*')) || (request()->is('settings/blocks*')) || (request()->is('settings/block-categories*'))
		|| (request()->is('settings/categories*')) || (request()->is('settings/groupcategories*')) ? 'show' : '' }}" aria-labelledby="headingTwo" data-parent="#accordionSidebar" style="">
		<div class="py-2 collapse-inner rounded">
			<a class="collapse-item {{ (request()->is('settings/templates*')) ? 'active' : '' }}" href="{{ route('settings.templates.index') }}">
				<span>@lang('Templates')</span>
			</a>
			<a class="collapse-item {{ (request()->is('settings/categories*')) ? 'active' : '' }}" href="{{ route('settings.categories.index') }}">
				<span>@lang('Template Categories')</span>
			</a>
			<a class="collapse-item {{ (request()->is('settings/groupcategories*')) ? 'active' : '' }}" href="{{ route('settings.groupcategories.index') }}">
				<span>@lang('Group Categories')</span>
			</a>
			<a class="collapse-item {{ (request()->is('settings/blocks*')) ? 'active' : '' }}" href="{{ route('settings.blocks.index') }}">
				<span>@lang('Blocks')</span>
			</a>
			<a class="collapse-item {{ (request()->is('settings/block-categories*')) ? 'active' : '' }}" href="{{ route('settings.block-categories.index') }}">
				<span>@lang('Block Categories')</span>
			</a>
		</div>
	</div>
</li>
@endcan