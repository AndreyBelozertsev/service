<?php

namespace App\Providers;

use SleepingOwl\Admin\Providers\AdminSectionsServiceProvider as ServiceProvider;

class AdminSectionsServiceProvider extends ServiceProvider
{

    /**
     * @var array
     */
    protected $sections = [
        \App\Models\User::class => 'App\Http\Sections\User',
        \App\Models\Client::class => 'App\Http\Sections\Client',
        \App\Models\Report::class => 'App\Http\Sections\Report',
        \App\Models\Role::class => 'App\Http\Sections\Role',
        \App\Models\Permission::class => 'App\Http\Sections\Permission',

    ];

    protected $widgets = [
        \App\Widgets\Logout::class,
        \App\Widgets\Dashboard::class,
    ];

    protected $policies = [
        \App\Http\Sections\User::class => \App\Policies\UserPolicy::class,
        \App\Http\Sections\Client::class => \App\Policies\ClientPolicy::class,
        \App\Http\Sections\Permission::class => \App\Policies\PermissionPolicy::class,
        \App\Http\Sections\Report::class => \App\Policies\ReportPolicy::class,
        \App\Http\Sections\Role::class => \App\Policies\RolePolicy::class,

    ];

    /**
     * Register sections.
     *
     * @param \SleepingOwl\Admin\Admin $admin
     * @return void
     */
    public function boot(\SleepingOwl\Admin\Admin $admin)
    {
        // Регистрация виджетов в реестре
        /** @var WidgetsRegistryInterface $widgetsRegistry */
        $widgetsRegistry = $this->app[\SleepingOwl\Admin\Contracts\Widgets\WidgetsRegistryInterface::class];
 
        foreach ($this->widgets as $widget) {
            $widgetsRegistry->registerWidget($widget);
        }
        $this->registerPolicies();

        parent::boot($admin);
    }
}
