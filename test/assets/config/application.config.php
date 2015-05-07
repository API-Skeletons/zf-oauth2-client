<?php

return array(
    'modules' => array(
        'ZF\OAuth2\Client',
    ),
    'module_listener_options' => array(
        'config_glob_paths' => array(
            __DIR__ . '/testing.config.php',
        ),
        'module_paths' => array(
#            'ZFTestApigilityGeneral' => __DIR__ . '/../../assets/module/General',
#            'ZFTestApigilityDb' => __DIR__ . '/../assets/module/Db',
#            'ZFTestApigilityDbApi' => __DIR__ . '/../assets/module/DbApi',
        ),
    ),
);
