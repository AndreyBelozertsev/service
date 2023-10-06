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

    ];

    protected $widgets = [
        \App\Widgets\Logout::class,
        \App\Widgets\Dashboard::class,
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

        parent::boot($admin);
    }
}
