<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'ZF\OAuth2\Client\Controller\OAuth2' => 'ZF\OAuth2\Client\Controller\OAuth2Controller',
            'ZF\OAuth2\Client\Controller\Jwt' => 'ZF\OAuth2\Client\Controller\JwtController',
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

    'console' => array(
        'router' => array(
            'routes' => array(
                'generate-jwt' => array(
                    'options' => array(
                        'route'    => 'oauth2:jwt:generate',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Client\Controller\Jwt',
                            'action'     => 'generate'
                        ),
                    ),
                ),
            ),
        ),
    ),
);
