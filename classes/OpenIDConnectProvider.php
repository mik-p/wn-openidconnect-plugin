<?php

namespace mikp\openidconnect\Classes;

use Jumbojett\OpenIDConnectClient;
use Hybridauth\User;

class OpenIDConnectProvider extends OpenIDConnectClient
{

    public function isConnected()
    {
        return true;
    }

    public function disconnect()
    {
        return true;
    }

    public function getUserProfile()
    {
        $userProfile = new User\Profile();

        $userProfile->identifier  = $this->requestUserInfo('user_id');
        $userProfile->email       = $this->requestUserInfo('email');
        $userProfile->firstName   = $this->requestUserInfo('given_name');
        $userProfile->lastName    = $this->requestUserInfo('family_name');
        $userProfile->displayName = $this->requestUserInfo('name');
        $userProfile->photoURL    = $this->requestUserInfo('picture');
        $userProfile->profileURL  = $this->requestUserInfo('profile');

        return $userProfile;
    }
}
