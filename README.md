ZF OAuth2 Client
================

[![Build Status](https://travis-ci.org/TomHAnderson/zf-oauth2-client.svg?branch=0.1.0)](https://travis-ci.org/TomHAnderson/zf-oauth2-client)
[![Coverage Status](https://coveralls.io/repos/TomHAnderson/zf-oauth2-client/badge.svg)](https://coveralls.io/r/TomHAnderson/zf-oauth2-client)
[![Total Downloads](https://poser.pugx.org/zfcampus/zf-oauth2-client/downloads)](https://packagist.org/packages/zfcampus/zf-oauth2-client) 

This client is written to connect to zf-oauth2 specifically.

When you write an application which includes
[zfcampus/zf-oauth2](https://github.com/zfcampus/zf-oauth2)
this module is written to connect easily and cleanly with your application.


Install
-------

Installation of this module uses composer. For composer documentation, please refer to [getcomposer.org](http://getcomposer.org/).

```sh
$ php composer.phar require zfcampus/zf-oauth2-client "~0.1"
```

Add this module to your application's configuration:

```php
'modules' => array(
   ...
   'ZF\OAuth2\Client',
),
```

This module provides the service manager config through the module but you may use the `ZF\OAuth2\Client\OAuth2Client``` class directly by injecting your own `Zend\Http\Client` and configuration.


Configuration
-------------

Copy `config/zf-oauth2-client.global.php.dist` to `config/autoload/zf-oauth2-client-global.php` and edit.
You may configure multiple zf-oauth2 authorization code providers.

```php
    'zf-oauth2-client' => array(
        'default' => array(
            'client_id' => 'client',
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
    $scope = 'read';
    $oauth2Client = $this->getServiceLocator()->get('zf_oauth2_client');

    return $this->plugin('redirect')
        ->toUrl($oauth2Client->getAuthorizationCodeUri('default', $state, $scope));
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

The response from validate is a stdClass object and a direct respose from the zf-oauth2 server:
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
