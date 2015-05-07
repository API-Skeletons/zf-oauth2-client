ZF OAuth2 Client
================

This client is written to connect to zf-oauth2 specifically.

When you write an application which includes
[zfcampus/zf-oauth2](https://github.com/zfcampus/zf-oauth2)
this module is written to connect easily and cleanly with your application.


Install
-------


Configuration
-------------

Configuration is done through your config file of choosing.
You may configure multiple zf-oauth2 authorization code providers.
Simply add the following array to a config file:

```php
    'zf-oauth2-client' => array(
        'default' => array(
            'clientId' => 'client15',
            'secret' => 'password',
            'endpoint' => 'http://localhost:8081/oauth',
            'callback' => 'http://localhost:8082/application/oauth2/callback',
        ),
        /* 'other provider' => array( ... */
    ),
```


Use
---

You may choose to setup a controller action or incorporate this code into your login method
where 'default' is the configuration block for the authorization code provider:

```php
public function loginAction()
{
    $state = md5(rand());
    $oauth2Client = $this->getServiceLocator()->get('zf_oauth2_client');

    return $this->plugin('redirect')->toUrl($oauth2Client->getAuthorizationCodeUri('default', $state));
}
```

You will need a callback action too.  This is the uri listed above for 'callback'.

```php
public function callbackAction()
{
    $oauth2Client = $this->getServiceLocator()->get('zf_oauth2_client');
    $accessToken = $oauth2Client->validate('default', $this->getRequest()->getQuery());

    die('valid access token received');
}
```

The response from validate is a stcClass object and a direct respose from the zf-oauth2 server:
```php
stdClass Object
(
    [access_token] => 3794b26015d87fb562068763071ec7850d7d2c2a
    [expires_in] => 3600
    [token_type] => Bearer
    [scope] =>
    [refresh_token] => 1b67b907f3779e8539df523a7c797c37927e5da6
)
```