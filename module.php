<?php

use NamelessMC\Framework\Extend;

return [
    (new Extend\Language(__DIR__ . '/language')),
    (new Extend\FrontendAssets)
        ->css(__DIR__ . '/assets/css/cookieconsent.min.css')
        ->js(__DIR__ . '/assets/js/configure.js', ['cookies'])
        ->js(__DIR__ . '/assets/js/cookieconsent.min.js'),
    (new Extend\FrontendLoading)
        ->hook(\NamelessMC\CookieConsent\FrontendLoadingHook::class),
    (new Extend\Sitemap) // TODO could this be done via FrontendPages ?
        ->path('/cookies', 0.9),
    (new Extend\FrontendPages)
        ->templateDirectory(__DIR__ . '/views')
        ->register('/cookies', 'cookies', 'cookie/cookie_notice', \NamelessMC\CookieConsent\Pages\Cookies::class, false),
    (new Extend\PanelPages)
        ->templateDirectory(__DIR__ . '/panel_views')
        ->register('/cookies', 'cookie_settings', 'cookie/cookies', \NamelessMC\CookieConsent\Pages\Panel\Cookies::class, 'admincp.cookies', 'fas fa-cookie-bite'),
    (new Extend\Permissions)
        ->register([
            'staffcp' => [
                'admincp.cookies' => 'cookie/cookies',
            ],
        ]),
];
