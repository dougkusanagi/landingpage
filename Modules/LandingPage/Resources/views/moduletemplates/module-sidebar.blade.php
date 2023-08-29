<li class="nav-item {{ (request()->is('dashboard')) ? 'active' : '' }}">
	<a class="nav-link" href="{{ route('dashboard') }}">
		<i class="fas fa-tachometer-alt"></i>
		<span>@lang('Dashboard')</span></a>
</li>
<li class="nav-item {{ (request()->is('landingpages*')) ? 'active' : '' }}">
	<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLandingPages" aria-expanded="false" aria-controls="collapseLandingPages">
		<i class="fas fa-paper-plane"></i>
		<span>@lang('Landing Pages')</span>
	</a>
	<div id="collapseLandingPages" class="collapse {{ (request()->is('landingpages*')) || (request()->is('alltemplates*')) ? 'show' : '' }}" aria-labelledby="collapseLandingPages">
		<div class="py-2 collapse-inner rounded">
			<a class="collapse-item {{ (request()->is('landingpages*')) ? 'active' : '' }}" href="{{ route('landingpages.index') }}">@lang('My Landing Pages')</a>
			<a class="collapse-item {{ (request()->is('alltemplates')) ? 'active' : '' }}" href="{{ route('alltemplates') }}">@lang('Create Landing Page')</a>
		</div>
	</div>
</li>