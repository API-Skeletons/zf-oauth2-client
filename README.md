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

This module provides the service manager config through the module but you may use the `ZF\OAuth2\Client\OAuth2Client` class directly by injecting your own `Zend\Http\Client` and configuration.


Configuration
-------------

Copy `config/zf-oauth2-client.global.php.dist` to `config/autoload/zf-oauth2-client-global.php` and edit.
You may configure multiple zf-oauth2 authorization code provider profiles.  login_redirect_route is your
authentication route.

```php
    'zf-oauth2-client' => array(
        'profiles' => array(
            'default' => array(
                'login_redirect_route' => 'zfcuser',
                'client_id' => 'client',
                'secret' => 'password',
                'endpoint' => 'http://localhost:8081/oauth',
                'scope' = 'list,of,scopes',
            ),
            /* 'other provider' => array( ... */
        ),
    ),
```


zf-oauth2 Server Configuration
--------------------

zf-oauth2-client expects the server to return a new refresh token anytime a refresh token is used to
get a new access token.  To set this flag on zf-oauth2 use

```php
return array(
    'zf-oauth2' => array(
        'options' => array(
            'always_issue_new_refresh_token' => true,
        ),
    ),
);
```


Use
---

A controller is provided to send the user into the authorization code process and validate the code
when the user returns.  Upon validation the session will have a valid access_token.

To send a user into the authorization code process redirect them (from a controller):

```php
$this->plugin('redirect')
    ->toRoute('zf-oauth2-client', array('profile' => 'default'));
```

When the user returns from the process they will be redirected to the login_redirect_route.  This route
should fetch an authorized http client and, using it, authenticate the user based on their profile
returned from an API call back to the OAuth2 server.
