<?php

namespace ZF\OAuth2\Client\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use OAuth\Common\Http\Uri\UriFactory;
use Zend\Authentication\AuthenticationService;
use ZF\OAuth2\Client\Exception\ValidateException;

class OAuth2Controller extends AbstractActionController
{
    public function logoutAction()
    {
        return $this->plugin('redirect')->toRoute('home');
    }

    public function loginAction()
    {
        $oAuth2Service = $this->getServiceLocator()->get('ZF\OAuth2\Client\Service\OAuth2Service');

        if (!empty($this->getRequest()->getQuery('code'))) {
            // This is a callback request with a code
            $oAuth2Service->validate($this->params()->fromRoute('profile'), $this->getRequest()->getQuery());

            // Validation successful; use
            $oAuth2Service->getHttpBearerClient('default');

            die('access token received');

        } else {
            return $this->plugin('redirect')
                ->toUrl($oAuth2Service->getAuthorizationCodeUri(
                    'default',
                    $this->params()->fromRoute('scope')
                )
            );
        }
    }
}

