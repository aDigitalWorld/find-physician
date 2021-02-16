<?php

Breadcrumbs::for('admin.accounts.index', function ($trail) {
    $trail->push(__('labels.backend.access.accounts.management'), route('admin.accounts.index'));
});

Breadcrumbs::for('admin.accounts.deactivated', function ($trail) {
    $trail->parent('admin.accounts.index');
    $trail->push(__('menus.backend.access.accounts.deactivated'), route('admin.accounts.deactivated'));
});

Breadcrumbs::for('admin.account.deleted', function ($trail) {
    $trail->parent('admin.accounts.index');
    $trail->push(__('menus.backend.access.accounts.deleted'), route('admin.account.deleted'));
});

Breadcrumbs::for('admin.accounts.create', function ($trail) {
    $trail->parent('admin.accounts.index');
    $trail->push(__('labels.backend.access.accounts.create'), route('admin.accounts.create'));
});

Breadcrumbs::for('admin.account.show', function ($trail, $id) {
    $trail->parent('admin.accounts.index');
    $trail->push(__('menus.backend.access.accounts.view'), route('admin.account.show', $id));
});

Breadcrumbs::for('admin.account.edit', function ($trail, $id) {
    $trail->parent('admin.accounts.index');
    $trail->push(__('menus.backend.access.accounts.edit'), route('admin.account.edit', $id));
});
