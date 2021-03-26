<?php

namespace mikp\openidconnect\SocialLoginProviders;

use Backend\Widgets\Form;
use mikp\openidconnect\Classes\OpenIDConnectProvider;
use Flynsarmy\SocialLogin\SocialLoginProviders\SocialLoginProviderBase;
use URL;

class OpenIDConnect extends SocialLoginProviderBase
{
    use \October\Rain\Support\Traits\Singleton;

    protected $driver = 'OpenIDConnect';
    protected $adapter;
    protected $callback;

    /**
     * Initialize the singleton free from constructor parameters.
     */
    protected function init()
    {
        parent::init();
        $this->callback = URL::route('flynsarmy_sociallogin_provider_callback', ['OpenIDConnect'], true);
    }

    public function getAdapter()
    {
        if ( !$this->adapter )
        {
            // Instantiate adapter using the configuration from our settings page
            $providers = $this->settings->get('providers', []);

            $this->adapter = new OpenIDConnectProvider(
                @$providers['OpenIDConnect']['id_provider'],
                @$providers['OpenIDConnect']['client_id'],
                @$providers['OpenIDConnect']['client_secret']
            );

            $this->adapter->setVerifyHost(false);
            $this->adapter->setVerifyPeer(false);
            // $this->adapter->setCertPath('/path/to/my.cert');
        }

        return $this->adapter;
    }

    public function isEnabled()
    {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['OpenIDConnect']['enabled']);
    }

    public function isEnabledForBackend()
    {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['OpenIDConnect']['enabledForBackend']);
    }

    public function extendSettingsForm(Form $form)
    {
        $form->addFields([
            'noop' => [
                'type' => 'partial',
                'path' => '$/mikp/openidconnect/partials/backend/forms/settings/_openidconnect_info.htm',
                'tab' => 'OpenIDConnect',
            ],

            'providers[OpenIDConnect][enabled]' => [
                'label' => 'Enabled on frontend?',
                'type' => 'checkbox',
                'comment' => 'Can frontend users log in with OpenID Connect?',
                'default' => 'true',
                'span' => 'left',
                'tab' => 'OpenIDConnect',
            ],

            'providers[OpenIDConnect][enabledForBackend]' => [
                'label' => 'Enabled on backend?',
                'type' => 'checkbox',
                'comment' => 'Can administrators log into the backend with OpenID Connect?',
                'default' => 'false',
                'span' => 'right',
                'tab' => 'OpenIDConnect',
            ],

            'providers[OpenIDConnect][id_provider]' => [
                'label' => 'ID Provider',
                'type' => 'text',
                'tab' => 'OpenIDConnect',
            ],

            'providers[OpenIDConnect][client_id]' => [
                'label' => 'App ID',
                'type' => 'text',
                'tab' => 'OpenIDConnect',
            ],

            'providers[OpenIDConnect][client_secret]' => [
                'label' => 'Private Key',
                'type' => 'text',
                'tab' => 'OpenIDConnect',
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

        $token = [$this->getAdapter()->getAccessToken()];
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
