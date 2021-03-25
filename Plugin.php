<?php namespace MikP\Openid;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public $elevated = true;

	public $require = ['RainLab.User', 'Flynsarmy.SocialLogin'];
	
    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }
    
    public function  register_flynsarmy_sociallogin_providers()
    {
        return [
            '\\MikP\\Openid\\SocialLoginProviders\\Ok' => [
                'label' => 'OpenID',
                'alias' => 'OpenID',
                'description' => 'Log in with OpendID Connect'
            ],
        ];
    }
}
