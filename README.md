# php-facebook-google-login
PHP Classes for Facebook and Google Login 


#run dependencies

Facebook : composer require facebook/graph-sdk
Google : composer require google/apiclient 

#get login urls
Facebook :

getLoginUrl for login page:
$fb_auth = new Facebook_Auth();
$fb_auth->getLoginUrl();

on callbackpage get userdata: (callback page will specified in $callback_url in Facebook_Auth class)
$fb_auth = new Facebook_Auth();
$fb_auth->getLoggedInUser();


For Google Auth user Google_Auth class rest will be the same.
