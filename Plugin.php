<?php namespace mikp\openidconnect;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public $elevated = true;

	public $require = ['Winter.User', 'Flynsarmy.SocialLogin'];

    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    public function  register_flynsarmy_sociallogin_providers()
    {
        return [
            '\\mikp\\openidconnect\\SocialLoginProviders\\OpenIDConnect' => [
                'label' => 'OpenIDConnect',
                'alias' => 'OpenIDConnect',
                'description' => 'Log in with OpendID Connect'
            ],
        ];
    }
}
