<?php

require_once 'vendor/autoload.php';

class Google_Auth
{
    protected $client;

    public $final_redirect_url = "http://local.example.com";
    
    public function __construct()
    {
        $this->client = $this->initGoogleClient();
    }
    
    public function initGoogleClient()
    {
        $clientId     = '15676014225-t1b24aq6aipke18siomaktjf2s2juj9j.apps.googleusercontent.com';
        $clientSecret = 'l05Kq6njC4c9XuFo6YZEroCO';
        $redirectURL  = 'http://local.example.com/google-callback.php';
        
        //Call Google API
        $gClient = new Google_Client();
        $gClient->setApplicationName('Login to CodexWorld.com');
        $gClient->setClientId($clientId);
        $gClient->setClientSecret($clientSecret);
        $gClient->setRedirectUri($redirectURL);
        $gClient->setScopes('profile');
        
        return $gClient;
    }
    
    public function isLoggedGoogleIn()
    {
        return isset($_SESSION['google_access_token']);
    }
    
    public function getLoginUrl()
    {
        return $this->client->createAuthUrl();
    }
    
    public function callback()
    {
        if (isset($_GET['code'])) {
            $this->client->authenticate($_GET['code']);
            $this->setToken($this->client->getAccessToken());
            return true;
        }
        return false;
    }
    
    public function setToken($token)
    {
        $_SESSION['google_access_token'] = $token;
        $this->client->setAccessToken($token);
    }
    
    public function getLoggedInUser()
    {
        $payload = $this->client->verifyIdToken();
        if (!empty($payload)) {
            return $payload;
        }
        return array();
    }
    
    public function getLogoutUrl()
    {
        return "https://www.google.com/accounts/Logout?continue=https://appengine.google.com/_ah/logout?continue=".$this->final_redirect_url;
    }
}