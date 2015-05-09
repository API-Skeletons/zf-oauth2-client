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
                    'route'    => '/oauth2/client/:profile',
                    'defaults' => array(
                        'controller' => 'ZF\OAuth2\Client\Controller\OAuth2',
                        'action'     => 'login',
                    ),
                ),
            ),
        ),
    ),

    'service_manager' => array(
        'invokables' => array(
            'ZF\OAuth2\Client\Http' => 'Zend\Http\Client',
            'ZF\OAuth2\Client\HttpBearer' => 'Zend\Http\Client',
        ),
    ),
);
