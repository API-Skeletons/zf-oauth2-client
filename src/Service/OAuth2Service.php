<?php

namespace ZF\OAuth2\Client\Service;

use Zend\Http;
use Zend\Json\Json;
use Exception;
use Zend\Session\Container;
use Zend\View\HelperPluginManager;
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

        return $this->httpBearerClient;
    }

    public function setPluginManager($manager)
    {
        $this->pluginManager = $manager;
    }

    public function getPluginManager()
    {
        return $this->pluginManager;
    }

    /**
     * Return an access code from an OAuth2 request callback
     */
    public function validate($profile, $query)
    {
        $config = $this->getConfig();

        $container = new Container("OAuth2_Client_$profile");
        if ($container->state !== $query['state']) {
            // @codeCoverageIgnoreStart
            throw new ValidateException("Application state changed during OAuth2 authorization");
        }
            // @codeCoverageIgnoreEnd

        $urlPlugin = $this->getPluginManager()->get('url');
        $redirectUri = $urlPlugin->fromRoute(
            'zf-oauth2-client',
            array('profile' => $profile),
            array('force_canonical' => true)
        );

        $client = $this->getHttpClient();
        $client->setUri($config[$profile]['endpoint']);
        $client->setMethod('POST');
        $client->setHeaders(array(
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ));
        $client->setRawBody(Json::encode(array(
            'grant_type' => 'authorization_code',
            'client_id' => $config[$profile]['clientId'],
            'client_secret' => $config[$profile]['secret'],
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
    public function getAuthorizationCodeUri($profile, $state = '', $scope = '')
    {
        $config = $this->getConfig();

        $container = new Container("OAuth2_Client_$profile");
        $container->state = $state;

        $uri = \Zend\Uri\UriFactory::factory(
            $config[$profile]['endpoint'] . '/authorize'
        );

        $urlPlugin = $this->getPluginManager()->get('url');

        $redirectUri = $urlPlugin->fromRoute(
            'zf-oauth2-client',
            array('profile' => $profile),
            array('force_canonical' => true)
        );

        $uri->setQuery(array(
            'client_id' => $config[$profile]['clientId'],
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'state' => $state,
        ));

        return $uri;
    }
}