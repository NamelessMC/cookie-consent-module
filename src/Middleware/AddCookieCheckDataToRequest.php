<?php

namespace NamelessMC\CookieConsent\Middleware;

class AddCookieCheckDataToRequest extends \NamelessMC\Framework\Pages\Middleware
{
    private \Cache $cache;
    private \TemplateBase $template;
    private \Language $cookiesLanguage;
    private \Smarty $smarty;

    public function __construct(
        \Cache $cache,
        \TemplateBase $template,
        \Language $cookiesLanguage,
        \Smarty $smarty
    ) {
        $this->cache = $cache;
        $this->template = $template;
        $this->cookiesLanguage = $cookiesLanguage;
        $this->smarty = $smarty;
    }

    public function handle(): void
    {
        $this->cache->setCache('cookie_consent_module_cache');
        $options = $this->cache->fetch('options', function () {
            return ['type' => 'opt-in', 'position' => 'bottom-right'];
        });

        $cookie_url = \URL::build('/cookies');

        $this->template->addJSScript(
            $this->generateScript(
                array_merge($options, [
                    'cookies' => $this->cookiesLanguage->get('cookie', 'cookies'),
                    'message' => $this->cookiesLanguage->get('cookie', 'cookie_popup'),
                    'dismiss' => $this->cookiesLanguage->get('cookie', 'cookie_popup_disallow'),
                    'allow' => $this->cookiesLanguage->get('cookie', 'cookie_popup_allow'),
                    'link' => $this->cookiesLanguage->get('cookie', 'cookie_popup_more_info'),
                    'href' => $cookie_url,
                ])
            )
        );

        $this->smarty->assign([
            'COOKIE_URL' => $cookie_url,
            'COOKIE_NOTICE_HEADER' => $this->cookiesLanguage->get('cookie', 'cookie_notice'),
            'COOKIE_NOTICE_BODY' => $this->cookiesLanguage->get('cookie', 'cookie_notice_info'),
            'COOKIE_NOTICE_CONFIGURE' => $this->cookiesLanguage->get('cookie', 'configure_cookies'),
            'COOKIE_DECISION_MADE' => (bool) \Cookie::get('cookieconsent_status'),
        ]);

        define('COOKIE_CHECK', true);
        define('COOKIES_ALLOWED', \Cookie::exists('cookieconsent_status') && \Cookie::get('cookieconsent_status') == 'allow');
    }

    private function generateScript(array $options): string {
        $script_options = [];
        $background_colour = '#000';
        $text_colour = '#000';
        $button_text_colour = '#f1d600';
        $border_colour = '#f1d600';

        if (
            isset($options['position'])
            && in_array($options['position'], ['top', 'top_static', 'bottom-left', 'bottom-right'])
        ) {
            if ($options['position'] == 'top_static') {
                $script_options['position'] = 'bottom-right';
            } else {
                $script_options['position'] = $options['position'];
            }
        }

        if (isset($options['colours'])) {
            if ($options['colours']['background']) {
                $background_colour = \Output::getClean($options['colours']['background']);
            }

            if ($options['colours']['text']) {
                $text_colour = \Output::getClean($options['colours']['text']);
            }

            if ($options['colours']['button_text']) {
                $button_text_colour = \Output::getClean($options['colours']['button_text']);
            }

            if ($options['colours']['border']) {
                $border_colour = \Output::getClean($options['colours']['border']);
            }
        }

        if (
            isset($options['theme'])
            && in_array($options['theme'], ['classic', 'edgeless'])
        ) {
            $script_options['theme'] = $options['theme'];
        }

        if (isset($options['type'])
            && in_array($options['type'], ['opt-out', 'opt-in'])
        ) {
            $script_options['type'] = $options['type'];
        }

        $script_options['palette'] = [
            'button' => ['background' => 'transparent', 'text' => $button_text_colour, 'border' => $border_colour],
            'popup' => ['background' => $background_colour, 'text' => $text_colour],
        ];

        $script_options['content'] = [
            'policy' => $options['cookies'],
            'message' => $options['message'],
            'deny' => $options['dismiss'],
            'allow' => $options['allow'],
            'link' => $options['link'],
            'href' => $options['href'],
        ];

        $json = json_encode($script_options, JSON_PRETTY_PRINT);

        $file = file_get_contents(__DIR__ . '/../assets/frontend/js/template.js');
        dd($file);
        return str_replace(
            '//"{x}"',
            substr($json, 1, -1),
            file_get_contents(__DIR__ . '/../assets/frontend/js/template.js')
        );
    }
}