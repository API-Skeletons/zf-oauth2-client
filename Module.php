<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Client;

use ZF\OAuth2\Client\Service\OAuth2Service;
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
                'ZF\OAuth2\Client\Service\OAuth2Service' => function ($services) {
                    $config = $services->get('Config');
                    $config = $config['zf-oauth2-client'];

                    $client = new OAuth2Service();
                    $client->setConfig($config);
                    $client->setHttpClient($services->get('ZF\OAuth2\Client\Http'));
                    $client->setHttpBearerClient($services->get('ZF\OAuth2\Client\HttpBearer'));
                    $client->setPluginManager($services->get('ControllerPluginManager'));

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
