<?php

namespace ZF\OAuth2\Client\Service;

use Zend\Http;
use Zend\Json\Json;
use Exception;
use Zend\Session\Container;
use Zend\Mvc\Controller\PluginManager;
use ZF\OAuth2\Client\Exception\ValidateException;
use Datetime;
use DateInterval;

class OAuth2Service
{
    protected $config;
    protected $httpClient;
    protected $httpBearerClient;
    protected $pluginManager;

    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setHttpClient(Http\Client $client)
    {
        $this->httpClient = $client;

        return $this;
    }

    public function getHttpClient()
    {
        return $this->httpClient;
    }

    public function setHttpBearerClient(Http\Client $client)
    {
        $this->httpBearerClient = $client;

        return $this;
    }

    public function getHttpBearerClient($profile)
    {
        $container = new Container("OAuth2_Client_$profile");

        // Use refresh token if access token is expired
        if ($container->expires <= new Datetime()) {
            $this->refresh($profile);
        }

        $this->httpBearerClient->setHeaders(array(
            'Authorization' => 'Bearer ' . $container->access_token,
        ));

        return $this->httpBearerClient;
    }

    public function setPluginManager(PluginManager $manager)
    {
        $this->pluginManager = $manager;

        return $this;
    }

    public function getPluginManager()
    {
        return $this->pluginManager;
    }

    /**
     * Obtain a new access token using the refresh token
     */
    public function refresh($profile)
    {
        $container = new Container("OAuth2_Client_$profile");
        $config = $this->getConfig();

        $client = $this->getHttpClient();
        $client->setUri($config['profiles'][$profile]['endpoint']);
        $client->setMethod('POST');
        $client->setHeaders(array(
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ));
        $client->setRawBody(Json::encode(array(
            'grant_type' => 'refresh_token',
            'client_id' => $config['profiles'][$profile]['client_id'],
            'client_secret' => $config['profiles'][$profile]['secret'],
            'refresh_token' => $container->refresh_token,
        )));

        $response = $client->send();

        $body = Json::decode($response->getBody(), true);

        $container->access_token = $body['access_token'];
        $container->expires_in = $body['expires_in'];
        $container->token_type = $body['token_type'];
        $container->scope = $body['scope'];
        $container->refresh_token = $body['refresh_token'];

        return;
    }

    /**
     * Return an access code from an OAuth2 request callback
     */
    public function validate($profile, $query)
    {
        $config = $this->getConfig();

        // Validate the application state
        $container = new Container("OAuth2_Client_$profile");
        if ($container->state !== $query['state']) {
            // @codeCoverageIgnoreStart
            throw new ValidateException("Application state changed during OAuth2 authorization");
        } else {
            // @codeCoverageIgnoreEnd
            $container->state = null;
        }

        // Build redirect uri
        $urlPlugin = $this->getPluginManager()->get('url');
        $redirectUri = $urlPlugin->fromRoute(
            'zf-oauth2-client',
            array('profile' => $profile),
            array('force_canonical' => true)
        );

        // Exchange the authorization code for an access token
        $client = $this->getHttpClient();
        $client->setUri($config['profiles'][$profile]['endpoint']);
        $client->setMethod('POST');
        $client->setHeaders(array(
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ));
        $client->setRawBody(Json::encode(array(
            'grant_type' => 'authorization_code',
            'client_id' => $config['profiles'][$profile]['client_id'],
            'client_secret' => $config['profiles'][$profile]['secret'],
            'redirect_uri' => $redirectUri,
            'code' => $query['code'],
        )));
        $response = $client->send();

        $body = Json::decode($response->getBody(), true);
        if ($response->getStatusCode() !== 200) {
            // @codeCoverageIgnoreStart
            throw new ValidateException($body['detail'], $body['status']);
        }
            // @codeCoverageIgnoreEnd

        // Save the access token to the session
        $container->access_token = $body['access_token'];
        $container->expires_in = $body['expires_in'];
        $container->token_type = $body['token_type'];
        $container->scope = $body['scope'];
        $container->refresh_token = $body['refresh_token'];

        $expires = new Datetime();
        $expires = $expires->add(new DateInterval('PT' . $container->expires_in . 'S'));
        $container->expires = $expires;

        return $body;
    }

    /**
     * Return an URI object to request an OAuth2 authorization_code
     *
     * @param $profile string
     * @param $state string
     * @param $scope string
     * @return Zend\Uri\Uri $uri;
     */
    public function getAuthorizationCodeUri($profile, $scope = '')
    {
        $config = $this->getConfig();

        $container = new Container("OAuth2_Client_$profile");
        $container->state = md5(rand());

        $uri = \Zend\Uri\UriFactory::factory(
            $config['profiles'][$profile]['endpoint'] . '/authorize'
        );

        $urlPlugin = $this->getPluginManager()->get('url');

        $redirectUri = $urlPlugin->fromRoute(
            'zf-oauth2-client',
            array('profile' => $profile),
            array('force_canonical' => true)
        );

        $uri->setQuery(array(
            'client_id' => $config['profiles'][$profile]['client_id'],
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'state' => $container->state,
        ));

        return $uri;
    }
}