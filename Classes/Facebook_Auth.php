<?php
require_once("/vendor/facebook/graph-sdk/src/Facebook/autoload.php");

class Facebook_Auth
{
    private $app_id = "102394366920993"; // your app_id generated from developers.facebook.com
    private $app_secret = "ba5e5ec25fc94c93a2b220732adb39d1"; // your app_secret
    private $version = "v2.8"; //version
    public $callback_url = "http://local.example.com/facebook-callback"; // callbackurl
    public $final_redirect_url = "http://local.example.com";// after logout will redirect to this url
    
    protected $helper;
    protected $fb;

    //user permissions
    public $permissions = ['email', 'user_about_me', 'user_birthday', 'user_location', 'user_hometown'];
    
    public $AccessToken;
    
    public function __construct()
    {
        self::initFbClass();
    }
    
    public function initFbClass()
    {
        $fb = new Facebook\Facebook([
            'app_id' => $this->app_id,
            'app_secret' => $this->app_secret,
            'default_graph_version' => $this->version
        ]);
        $this->fb = $fb;
        $this->helper = $fb->getRedirectLoginHelper();
    }
    
    public function getLoginUrl()
    {
        return $this->helper->getLoginUrl($this->callback_url, $this->permissions);
    }
    
    public function callback()
    {
        //required to set $_SESSION['FBRLH_state'] for session issue
        if (isset($_GET['state'])) {
            $_SESSION['FBRLH_state'] = $_GET['state'];
        }
        
        try {
            $accessToken = $this->helper->getAccessToken();
        }
        catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        }
        catch (Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        
        if (!isset($accessToken)) {
            if ($this->helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $this->helper->getError() . "\n";
                echo "Error Code: " . $this->helper->getErrorCode() . "\n";
                echo "Error Reason: " . $this->helper->getErrorReason() . "\n";
                echo "Error Description: " . $this->helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }
        
        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $this->fb->getOAuth2Client();
        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        // Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateAppId($this->app_id);        
        $tokenMetadata->validateExpiration();
        

        if (!$accessToken->isLongLived()) {
            // Exchanges a short-lived access token for a long-lived one
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            }
            catch (Facebook\Exceptions\FacebookSDKException $e) {
                echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
                exit;
            }
        }
        
        $_SESSION['fb_access_token'] = (string) $accessToken;
        
        $this->AccessToken = $_SESSION['fb_access_token'];
    }
    
    public function getLoggedInUser()
    {
        $user_req_arr = ['id','name','birthday','currency','email','locale','location'];
        $response     = $this->fb->get('/me?fields=' . implode(",", $user_req_arr), $this->AccessToken);
        $user         = $response->getGraphUser();
        return $user;
    }
    
    public function logoutUrl()
    {
        return $this->helper->getLogoutUrl($this->AccessToken, $this->final_redirect_url);
    }
}