<?php

namespace NamelessMC\CookieConsent\Pages;

use NamelessMC\Framework\Pages\FrontendPage;

use \Smarty;
use \Language;
use \Output;

class Cookies extends FrontendPage {

    private \Smarty $smarty;
    private \Language $cookiesLanguage;

    public function __construct(
        \Smarty $smarty
    ) {
        $this->smarty = $smarty;
        $this->cookiesLanguage = \Illuminate\Container\Container::getInstance()->get('cookiesLanguage');
    }

    public function pageName(): string {
        return 'cookies';
    }

    public function viewFile(): string {
        return 'cookies/cookies.tpl';
    }

    public function render() {
        // Retrieve cookie notice from database
        $cookieNotice = \DB::getInstance()->query('SELECT value FROM nl2_privacy_terms WHERE `name` = ?', ['cookies'])->first()->value;

        $this->smarty->assign([
            'COOKIE_NOTICE_HEADER' => $this->cookiesLanguage->get('cookie', 'cookie_notice'),
            'COOKIE_NOTICE' => \Output::getPurified($cookieNotice),
            'UPDATE_SETTINGS' => $this->cookiesLanguage->get('cookie', 'update_settings'),
        ]);
    }
}
