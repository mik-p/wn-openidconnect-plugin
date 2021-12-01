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
        // make request for all user info
        $user_info = $this->requestUserInfo();

        // fill the user profile object
        $userProfile = new User\Profile();

        // throw an error if there is no email available
        if (!isset($user_info->email) || empty($user_info->email)) {
            print('missing user info - "email", not enough information to create a user');
            exit;
        } else { // set the email if it exists
            $userProfile->email = $user_info->email;
        }

        // set email verified
        if (isset($user_info->email_verified)) {
            $userProfile->emailVerified = $user_info->email_verified;
        }

        // check for and set the special identifier
        $userProfile->identifier  = $this->requestUserInfo('user_id');
        if (empty($userProfile->identifier) && isset($user_info->sub)) {
            $userProfile->identifier = $user_info->sub;
        } else {
            print('missing user info - "email", not enough information to create a user');
            exit;
        }

        // check all the required user variables exist and assign as needed
        if (isset($user_info->username)) {
            $userProfile->username = $user_info->username;
        }

        // display name
        if (isset($user_info->name)) {
            $userProfile->displayName = $user_info->name;
        }

        // last name
        if (isset($user_info->family_name)) {
            $userProfile->lastName = $user_info->family_name;
        } else {
            // set to 'viaOIDC' to indicate that it was not present from provider
            // and could not be made use of here
            $userProfile->lastName = 'viaOIDC';
        }

        // first name
        if (isset($user_info->given_name)) {
            $userProfile->firstName = $user_info->given_name;
        } else {
            // try set to the display name

            // remove any problematic special characters first
            $sanitary_name = str_replace(['/', '-'], '_', $userProfile->displayName);

            // split by space hopefully this will work and it will give a first and last name
            $space_split_display_name = explode(' ', $sanitary_name);

            if (count($space_split_display_name) == 2) {
                if (!empty($space_split_display_name[0]) && !empty($space_split_display_name[1])) {
                    $userProfile->firstName = $space_split_display_name[0];
                    $userProfile->lastName = $space_split_display_name[1];
                } else {
                    // set to email if that didn't work
                    $userProfile->firstName = $userProfile->email;
                    $userProfile->lastName = 'viaOIDC';
                }
            } else {
                // set to email if that didn't work
                $userProfile->firstName = $userProfile->email;
                $userProfile->lastName = 'viaOIDC';
            }
        }

        // other useful bits
        if (isset($user_info->picture)) {
            $userProfile->photoURL    = $user_info->picture;
        }
        if (isset($user_info->profile)) {
            $userProfile->profileURL  = $user_info->profile;
        }

        return $userProfile;
    }
}
