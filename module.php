<?php

use NamelessMC\Framework\Extend;

return [
    (new Extend\Language(__DIR__ . '/language')),
    (new Extend\FrontendAssets)
        ->css(__DIR__ . '/assets/frontend/css/cookieconsent.min.css')
        ->js(__DIR__ . '/assets/frontend/js/configure.js', ['cookies'])
        ->js(__DIR__ . '/assets/frontend/js/cookieconsent.min.js'),
    (new Extend\FrontendMiddleware)
        ->register(\NamelessMC\CookieConsent\Middleware\AddCookieCheckDataToRequest::class),
    (new Extend\Sitemap) // TODO could this be done via FrontendPages ?
        ->path('/cookies', 0.9),
    (new Extend\FrontendPages)
        ->register('/cookies', 'cookies', 'cookie/cookie_notice', \NamelessMC\CookieConsent\Pages\Cookies::class, false),
    (new Extend\PanelPages)
        ->register('/cookies', 'cookie_settings', 'cookie/cookies', \NamelessMC\CookieConsent\Pages\Panel\Cookies::class, 'admincp.cookies', 'fas fa-cookie-bite'),
    (new Extend\Permissions)
        ->register([
            'staffcp' => [
                'admincp.cookies' => 'cookie/cookies',
            ],
        ]),
];
