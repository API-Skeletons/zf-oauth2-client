<?php

namespace ZFTest\OAuth2\Client;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Http\Response;
use Datetime;
use Exception;
use Mockery as M;

class ClientTest extends AbstractHttpControllerTestCase
{
    protected $serviceManager;
    protected $state;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../assets/config/application.config.php'
        );
        parent::setUp();

        $this->serviceManager = $this->getApplication()->getServiceManager();
    }

    public function testGetAuthorizationCodeUri()
    {
        $this->state = md5(rand());
        $oauth2Client = $this->serviceManager->get('zf_oauth2_client');
        $uri = $oauth2Client->getAuthorizationCodeUri('default', $this->state);

        $expectedQuery = array(
            'client_id' => 'client_id',
            'redirect_uri' => 'http://localhost:8082/application/oauth2/callback',
            'scope' => '',
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'state' => $this->state,
        );

        $this->assertEquals($uri->getQueryAsArray(), $expectedQuery);
        $this->assertEquals($uri->getScheme(), 'http');
        $this->assertEquals($uri->getPath(), '/oauth/authorize');
        $this->assertEquals($uri->getHost(), 'localhost');
        $this->assertEquals($uri->getPort(), '8081');
    }

    public function testValidate()
    {
        $response = new Response;

#        print_r(get_class_methods($response));
#        die();

        $response->setContent(
            '{"access_token": "f1876e22c5fa2eedab1f02545c175639d649a406",'
            . '"expires_in": 3600,'
            . '"token_type": "Bearer",'
            . '"scope": "",'
            . '"refresh_token": "19a9bf36cc42c62ba3d4ae047746d589a77448dc"'
            . '}'
        );

        $mockHttpClient = M::mock('Zend\Http\Client');
        $mockHttpClient->shouldReceive('setUri')->once();
        $mockHttpClient->shouldReceive('setMethod')->once();
        $mockHttpClient->shouldReceive('setHeaders')->once();
        $mockHttpClient->shouldReceive('setRawBody')->once();
        $mockHttpClient->shouldReceive('send')->once()->andReturn($response);

        $this->state = md5(rand());
        $oauth2Client = $this->serviceManager->get('zf_oauth2_client');
        $oauth2Client->setHttpClient($mockHttpClient);
        $access_token = $oauth2Client->validate('default', array('code' => 'code', 'state' => $this->state));

        $compare = new \stdClass();
        $compare->access_token = 'f1876e22c5fa2eedab1f02545c175639d649a406';
        $compare->expires_in = 3600;
        $compare->token_type = 'Bearer';
        $compare->scope = '';
        $compare->refresh_token = '19a9bf36cc42c62ba3d4ae047746d589a77448dc';

        $this->assertEquals($access_token, $compare);
    }
}
