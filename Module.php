<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Client;

use ZF\OAuth2\Client\OAuth2Client as ZFOAuth2Client;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * ZF2 module
 */
class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ServiceProviderInterface
{
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'zf_oauth2_client' => function ($services) {
                    $config = $services->get('Config');
                    $config = $config['zf-oauth2-client'];

                    $httpClient = $services->get('ZF\OAuth2\Client\Http');

                    $client = new ZFOAuth2Client();
                    $client->setConfig($config);
                    $client->setHttpClient($httpClient);

                    return $client;
                }
            ),
        );
    }

    /**
     * Retrieve autoloader configuration
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array('Zend\Loader\StandardAutoloader' => array('namespaces' => array(
            __NAMESPACE__ => __DIR__ . '/src/',
        )));
    }

    /**
     * Retrieve module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
