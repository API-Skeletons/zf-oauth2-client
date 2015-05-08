<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'ZF\OAuth2\Client\Controller\OAuth2' => 'ZF\OAuth2\Client\Controller\OAuth2Controller',
        ),
    ),

    'router' => array(
        'routes' => array(
            'zf-oauth2-client' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/oauth2/client/:profile[/:scope]',
                    'defaults' => array(
                        'controller' => 'ZF\OAuth2\Client\Controller\OAuth2',
                        'action'     => 'login',
                    ),
                ),
            ),
        ),
    ),

    'service_manager' => array(
        'aliases' => array(
#            'ZF\OAuth2\Client\Http' => 'zf_oauth2_client_http',
            'ZF\OAuth2\Client\HttpBearer' => 'zf_oauth2_client_http',
        ),
        'invokables' => array(
            'zf_oauth2_client_http' => 'Zend\Http\Client',
            'zf_oauth2_client_bearer_http' => 'Zend\Http\Client',
        ),
    ),
);
