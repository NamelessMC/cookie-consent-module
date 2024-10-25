<?php

namespace NamelessMC\CookieConsent\Pages\Panel;

use Exception;
use NamelessMC\Framework\Pages\PanelPage;

class Cookies extends PanelPage
{
    private \Smarty $smarty;
    private \Language $coreLanguage;
    private \Language $cookiesLanguage;

    public function __construct(
        \Smarty $smarty,
        \Language $coreLanguage,
        \Illuminate\Container\Container $container,
    ) {
        $this->smarty = $smarty;
        $this->coreLanguage = $coreLanguage;
        $this->cookiesLanguage = $container->get('cookiesLanguage');
    }

    public function pageName(): string {
        return 'cookie_settings';
    }

    public function viewFile(): string {
        return 'cookies/cookies.tpl';
    }

    public function permission(): string {
        return 'admincp.cookies';
    }

    public function render() {
        if (\Input::exists()) {
            $errors = [];
        
            if (\Token::check()) {
                $validation = \Validate::check($_POST, [
                    'cookies' => [
                        \Validate::REQUIRED => true,
                        \Validate::MAX => 100000
                    ],
                ])->messages([
                    'cookies' => $this->cookiesLanguage->get('cookie', 'cookie_notice_error'),
                ]);
        
                if ($validation->passed()) {
                    try {
                        $cookie_id = \DB::getInstance()->get('privacy_terms', ['name', 'cookies'])->results();
                        if (count($cookie_id)) {
                            $cookie_id = $cookie_id[0]->id;
        
                            \DB::getInstance()->update('privacy_terms', $cookie_id, [
                                'value' => \Input::get('cookies')
                            ]);
                        } else {
                            \DB::getInstance()->insert('privacy_terms', [
                                'name' => 'cookies',
                                'value' => \Input::get('cookies')
                            ]);
                        }
        
                        $success = $this->cookiesLanguage->get('cookie', 'cookie_notice_success');
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                } else {
                    $errors = $validation->errors();
                }
            } else {
                $errors[] = $this->coreLanguage->get('general', 'invalid_token');
            }
        }

        if (isset($success)) {
            $this->smarty->assign([
                'SUCCESS' => $success,
                'SUCCESS_TITLE' => $this->coreLanguage->get('general', 'success')
            ]);
        }
        
        if (isset($errors) && count($errors)) {
            $this->smarty->assign([
                'ERRORS' => $errors,
                'ERRORS_TITLE' => $this->coreLanguage->get('general', 'error')
            ]);
        }

        // Get cookie notice
        $cookies = \DB::getInstance()->query('SELECT value FROM nl2_privacy_terms WHERE `name` = ?', ['cookies'])->first()->value;

        $this->smarty->assign([
            'DASHBOARD' => $this->coreLanguage->get('admin', 'dashboard'),
            'COOKIES' => $this->cookiesLanguage->get('cookie', 'cookies'),
            'TOKEN' => \Token::get(),
            'SUBMIT' => $this->coreLanguage->get('general', 'submit'),
            'COOKIE_NOTICE' => $this->cookiesLanguage->get('cookie', 'cookie_notice'),
            'COOKIE_NOTICE_VALUE' => \Output::getPurified($cookies),
        ]);
    }
}