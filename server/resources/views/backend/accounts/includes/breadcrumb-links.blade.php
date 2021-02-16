<li class="breadcrumb-menu">
    <div class="btn-group" role="group" aria-label="Button group">
        <div class="dropdown">
            <a class="btn dropdown-toggle" href="#" role="button" id="breadcrumb-dropdown-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@lang('menus.backend.access.accounts.main')</a>

            <div class="dropdown-menu" aria-labelledby="breadcrumb-dropdown-1">
                <a class="dropdown-item" href="{{ route('admin.accounts.index') }}">@lang('menus.backend.access.accounts.all')</a>
                <a class="dropdown-item" href="{{ route('admin.accounts.create') }}">@lang('menus.backend.access.accounts.create')</a>
                <a class="dropdown-item" href="{{ route('admin.accounts.deactivated') }}">@lang('menus.backend.access.accounts.deactivated')</a>
                <a class="dropdown-item" href="{{ route('admin.account.deleted') }}">@lang('menus.backend.access.accounts.deleted')</a>
            </div>
        </div><!--dropdown-->

        <!--<a class="btn" href="#">Static Link</a>-->
    </div><!--btn-group-->
</li>
