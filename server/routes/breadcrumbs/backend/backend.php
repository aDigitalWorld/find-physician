<?php

Breadcrumbs::for('admin.dashboard', function ($trail) {
    $trail->push(__('strings.backend.dashboard.title'), route('admin.dashboard'));
});
/*
Breadcrumbs::for('admin.accounts.index', function ($trail) {
    $trail->push(__('menus.backend.access.accounts.management'), route('admin.accounts.index'));
});

Breadcrumbs::for('admin.accounts.create', function ($trail) {
    $trail->parent('admin.acounts.index');
    $trail->push(__('menus.backend.access.accounts.create'), route('admin.accounts.create'));
});

Breadcrumbs::for('admin.acount.edit', function ($trail, $id) {
    $trail->parent('admin.accounts.index');
    $trail->push(__('menus.backend.access.accounts.edit'), route('admin.accounts.edit', $id));
});
*/
require __DIR__.'/auth.php';
require __DIR__.'/log-viewer.php';
