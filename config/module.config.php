<?php

return array(
    'service_manager' => array(
        'aliases' => array(
            'ZF\OAuth2\Client\Http' => 'Zend\Http\Client',
        ),
        'invokables' => array(
            'Zend\Http\Client' => 'Zend\Http\Client',
        ),
    ),
);
