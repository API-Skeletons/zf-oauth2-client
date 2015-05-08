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
        if (isset($_SESSION)) {
            unset($_SESSION);
            session_unset();
            session_destroy();
        }

        return $this->plugin('redirect')->toRoute('home');
    }

    public function loginAction()
    {
        $oAuth2Service = $this->getServiceLocator()->get('ZF\OAuth2\Client\Service\OAuth2Service');

        if (!empty($this->getRequest()->getQuery('code'))) {
            // This is a callback request with a code
            try {
                $oAuth2Service->validate($this->params()->fromRoute('profile'), $this->getRequest()->getQuery());
            } catch (ValidateException $e) {
                throw $e;
            }

            // Validation successful

        } else {
            $state = md5(rand());
            $scope = $this->params()->fromRoute('scope');

            return $this->plugin('redirect')
                ->toUrl($oAuth2Service->getAuthorizationCodeUri('default', $state, $scope));
        }

        die('fetched access token');



        $etsy = $this->getServiceLocator()->get('Etsy');

        // Create Etsy service
        if (!empty($_GET['oauth_token'])) {
            $token = $etsy->getStorage()->retrieveAccessToken('Etsy');

            try {

                // This was a callback request from Etsy, get the token
                $etsy->requestAccessToken(
                    $_GET['oauth_token'],
                    $_GET['oauth_verifier'],
                    $token->getRequestTokenSecret()
                );

                // Send a request now that we have access token
                $result = json_decode($etsy->request('/private/users/__SELF__'), true);

            } catch (\ErrorException $e) {
                return $this->plugin('redirect')->toRoute('login');
            } catch (\Exception $e) {
                return $this->plugin('redirect')->toRoute('login');
            }

            $auth = new AuthenticationService();
            $etsyAuthAdapter = $this->getServiceLocator()->get('EtsyAuthAdapter');
            $etsyAuthAdapter->setData($result);

            $result = $auth->authenticate($etsyAuthAdapter);

            return $this->plugin('redirect')->toRoute('user');

        } elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
            $response = $etsy->requestRequestToken();

            $extra = $response->getExtraParams();
            $url = $extra['login_url'];

            return $this->plugin('redirect')->toUrl($url);
        } else {
            $uriFactory = new UriFactory();
            $currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
            $currentUri->setQuery('');

            $url = $currentUri->getRelativeUri() . '?go=go';

            return $this->plugin('redirect')->toUrl($url);
        }

        return new ViewModel();
    }
}

