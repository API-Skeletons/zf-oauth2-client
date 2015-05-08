<?php

namespace ZF\OAuth2\Client\Service;

use Zend\Http;
use Zend\Json\Json;
use Exception;
use Zend\Session\Container;

class OAuth2Service
{
    protected $config;
    protected $httpClient;
    protected $bearerHttpClient;

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

    public function setBearerHttpClient(Http\Client $client)
    {
        $this->bearerHttpClient = $client;

        return $this;
    }

    public function getBearerHttpClient()
    {
        return $this->bearerHttpClient;
    }

    /**
     * Return an access code from an OAuth2 request callback
     */
    public function validate($profile, $query)
    {
        $config = $this->getConfig();

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
            'redirect_uri' => $config[$profile]['callback'],
            'code' => $query['code'],
        )));
        $response = $client->send();

        $body = Json::decode($response->getBody(), true);
        if ($response->getStatusCode() !== 200) {
            // @codeCoverageIgnoreStart
            throw new Exception($body['detail'], $body['status']);
        }
            // @codeCoverageIgnoreEnd

        // Save the access token to the session
        $container = new Container("OAuth2\Client\$profile");
        $container->access_token = $body['access_token'];
        $container->expires_in = $body['expires_in'];
        $container->token_type = $body['token_type'];
        $container->scope = $body['scope'];
        $container->refresh_token = $body['refresh_token'];

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

        $uri = \Zend\Uri\UriFactory::factory(
            $config[$profile]['endpoint'] . '/authorize'
        );

        $uri->setQuery(array(
            'client_id' => $config[$profile]['clientId'],
            'redirect_uri' => $config[$profile]['callback'],
            'scope' => $scope,
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'state' => $state,
        ));

        return $uri;
    }
}