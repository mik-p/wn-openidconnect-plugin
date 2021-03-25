<?php

namespace MikP\OpenID\SocialLoginProviders;

use Backend\Widgets\Form;
use MikP\OpenID\Classes\OpenIDProvider;
use Flynsarmy\SocialLogin\SocialLoginProviders\SocialLoginProviderBase;
use URL;

class Ok extends SocialLoginProviderBase
{
    use \October\Rain\Support\Traits\Singleton;

    protected $driver = 'OpenID';
    protected $adapter;
    protected $callback;

    /**
     * Initialize the singleton free from constructor parameters.
     */
    protected function init()
    {
        parent::init();
        $this->callback = URL::route('flynsarmy_sociallogin_provider_callback', ['OpenID'], true);
    }

    public function getAdapter()
    {
        if ( !$this->adapter )
        {
            // Instantiate adapter using the configuration from our settings page
            $providers = $this->settings->get('providers', []);

            $this->adapter = new OpenIDProvider([
                'callback' => $this->callback,

                'keys' => [
                    'key'     => @$providers['OpenID']['client_id'],
                    'secret' => @$providers['OpenID']['client_secret'],
                    'public' => @$providers['OpenID']['client_public'],
                ],

                'debug_mode' => config('app.debug', false),
                'debug_file' => storage_path('logs/flynsarmy.sociallogin.'.basename(__FILE__).'.log'),
            ]);
        }

        return $this->adapter;
    }

    public function isEnabled()
    {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['OpenID']['enabled']);
    }

    public function isEnabledForBackend()
    {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['OpenID']['enabledForBackend']);
    }

    public function extendSettingsForm(Form $form)
    {
        $form->addFields([
            'noop' => [
                'type' => 'partial',
                'path' => '$/MikP/OpenID/partials/backend/forms/settings/_openid_info.htm',
                'tab' => 'Ok',
            ],

            'providers[OpenID][enabled]' => [
                'label' => 'Enabled on frontend?',
                'type' => 'checkbox',
                'comment' => 'Can frontend users log in with OpenID Connect?',
                'default' => 'true',
                'span' => 'left',
                'tab' => 'Ok',
            ],

            'providers[OpenID][enabledForBackend]' => [
                'label' => 'Enabled on backend?',
                'type' => 'checkbox',
                'comment' => 'Can administrators log into the backend with OpenID Connect?',
                'default' => 'false',
                'span' => 'right',
                'tab' => 'Ok',
            ],

            'providers[OpenID][client_id]' => [
                'label' => 'App ID',
                'type' => 'text',
                'tab' => 'Ok',
            ],

            'providers[OpenID][client_public]' => [
                'label' => 'Public Key',
                'type' => 'text',
                'tab' => 'Ok',
            ],
            'providers[OpenID][client_secret]' => [
                'label' => 'Private Key',
                'type' => 'text',
                'tab' => 'Ok',
            ],
        ], 'primary');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider()
    {
        if ($this->getAdapter()->isConnected() )
            return \Redirect::to($this->callback);

        $this->getAdapter()->authenticate();
    }

    /**
     * Handles redirecting off to the login provider
     *
     * @return array ['token' => array $token, 'profile' => \Hybridauth\User\Profile]
     */
    public function handleProviderCallback()
    {
        $this->getAdapter()->authenticate();

        $token = $this->getAdapter()->getAccessToken();
        $profile = $this->getAdapter()->getUserProfile();

        // Don't cache anything or successive logins to different accounts
        // will keep logging in to the first account
        $this->getAdapter()->disconnect();

        return [
            'token' => $token,
            'profile' => $profile
        ];
    }
}