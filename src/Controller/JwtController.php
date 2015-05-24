<?php

namespace ZF\OAuth2\Client\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZF\OAuth2\Client\Service\Jwt;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use RuntimeException;

class JwtController extends AbstractActionController
{
    public function generateAction()
    {
        $console = $this->getServiceLocator()->get('console');
        $jwtService = new Jwt;

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest){
            throw new RuntimeException('You can only use this action from a console.');
        }

        $privateKeyPath = '';
        while (!file_exists($privateKeyPath)) {
            $privateKeyPath = Prompt\Line::prompt("Private Key path: ", false, 255);
        }
        $privateKey = file_get_contents($privateKeyPath);

        $iss = Prompt\Line::prompt("(iss) The issuer, usually the client_id: ", false, 255);
        $sub = Prompt\Line::prompt("(sub) The subject, usually a user_id: ", true, 255);
        $aud = Prompt\Line::prompt("(aud) The audience, usually the URI for the oauth server.  Not required.: ", true, 255);
        $exp = Prompt\Line::prompt("(exp) The expiration date in seconds since epoch. If the current time is"
            . " greater than the exp, the JWT is invalid.  Not required: ", true, 255);
        $nbf = Prompt\Line::prompt('(nbt) The "not before" time in seconds since epoch. If the current time is'
            . 'less than the nbf, the JWT is invalid.  Not required: ', true, 255);
        $jti = Prompt\Line::prompt('(jti) The "jwt token identifier", or nonce for this JWT.  Not Required: ', true, 255);

        $console->write($jwtService->generate($privateKey, $iss, $sub, $aud, $exp, $nbf, $jti) . "\n", Color::YELLOW);
    }
}
