<?php

namespace ZFTest\OAuth2\Client\Service;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Http\Response;
use Datetime;
use Exception;
use Mockery as M;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\EventManager\EventInterface;

class OAuth2ServiceTest extends AbstractHttpControllerTestCase implements InjectApplicationEventInterface
{
    protected $serviceManager;
    protected $state;
    protected $event;

    public function getEvent()
    {
        return $this->event;
    }

    public function setEvent(EventInterface $event)
    {
        $this->event = $event;

        return $this;
    }

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
        $oAuth2Service = $this->serviceManager->get('ZF\OAuth2\Client\Service\OAuth2Service');
        $uri = $oAuth2Service->getAuthorizationCodeUri('default', $this->state);


        $expectedQuery = array(
            'client_id' => 'client_id',
            'redirect_uri' => 'http://localhost:8082/oauth2/client/default',
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

        $oAuth2Service = $this->serviceManager->get('ZF\OAuth2\Client\Service\OAuth2Service');
        $oAuth2Service->setHttpClient($mockHttpClient);
        $access_token = $oAuth2Service->validate('default', array('code' => 'code', 'state' => $this->state));

        $compare = new \stdClass();
        $compare->access_token = 'f1876e22c5fa2eedab1f02545c175639d649a406';
        $compare->expires_in = 3600;
        $compare->token_type = 'Bearer';
        $compare->scope = '';
        $compare->refresh_token = '19a9bf36cc42c62ba3d4ae047746d589a77448dc';

        $this->assertEquals($access_token, $compare);
    }
}
