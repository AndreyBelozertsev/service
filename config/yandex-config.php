<?php

/**
 * Настройки Yandex Metrika API
 */
return [

    /**
     * OAuth Token
     */
    'token'          => env('YANDEX_METRIKA_API_TOKEN', ''),

    /**
     * Id счетчика Яндекс метрики
     */
    'counter_id'     => env('YANDEX_METRIKA_API_COUNTER_ID', 0),

    /**
     * Время жизни кэша в секундах
     */
    'cache_lifetime' => env('YANDEX_METRIKA_API_CACHE_LIFETIME', 0),

    'services' => [
        'websearch' => 'Яндекс.Поиск',
        'mobq' => 'Яедекс.Кью',
        'ruyandextraffic' => 'Яндекс.Карты моб. приложение',
        'ruyandexyandexmaps' => 'Яндекс.Карты моб. приложение',
        'ruyandexmobilenavigator' => 'Яндекс.Навигатор',
        'webmaps' => 'Яндекс.Карты',
        'ruyandexyandexnavi' => 'Яндекс.Навигатор',
        'mobservice' => 'Яндекс.Услуги: мобильная версия',
        'yandexbusinesssite' => 'Яндекс.Бизнес сайт'
    ],
    'params_types' => [
        'show_org' => 'name',
        'site' => 'name',
        'show-org' => 'name',
        'route' => 'name',
        'call' => 'name',
        'social' => 'name',
        'view_org_content' => 'name',
        'websearch' => 'service',
        'mobq' => 'service',
        'ruyandextraffic' => 'service',
        'ruyandexyandexmaps' => 'service',
        'ruyandexmobilenavigator' => 'service',
        'webmaps' => 'service',
        'ruyandexyandexnavi' => 'service',
        'mobservice' => 'service',
        'yandexbusinesssite' => 'service',
    ],

    'query_params2' => [
        'ym:s:vacuumOrganization' => 'address',
        'ym:s:vacuumSurface' => 'service'    
    ],

    'params_translate' => [
        'show_org' => 'Показ профиля',
        'view_org_content' => 'Показ профиля',
        'site' => 'Переход на сайт',
        'social' => 'Переход в соц сети',
        'show-org' => 'Показ профиля',
        'route' => 'Построение маршрута',
        'call' => 'Нажать на кнопку позвонить'
    ],

    'devices_translate' => [
        'mobile' => 'Мобильные устройства',
        'desktop'=> 'ПК',
        'tablet' => 'Планшеты',
        'tv'     => 'Телевизор'
    ],

    'periods' => [
        'current' => 'Текущий период',
        'previous' => 'Предыдущий период',
    ],

    'report_colors' => [
        'ff143f6a', 'ff91cf50', 'ff049544', 'ff548235', 'fff76238','ff750be8', 'ff0b253e'    
    ],
    'source_type' => [
        'direct' => 'Прямые заходы',
        'organic'=> 'Переходы из поисковых систем',
        'ad' => 'Переходы по рекламе',
        'referral' => 'Переходы по ссылкам на сайтах',
        'internal' => 'Внутренние переходы',
        'social' => 'Переходы из социальных сетей'
    ]
    
];
