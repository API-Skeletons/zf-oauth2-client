<?php

namespace ZF\OAuth2\Client\Service;

use OAuth2\Encryption\Jwt as ServerJwt;
use Datetime;
use Exception;

class Jwt
{
    /**
     * Generate a JWT
     * http://bshaffer.github.io/oauth2-server-php-docs/grant-types/jwt-bearer/
     *
     * @param $privateKey The private key to use to sign the token
     * @param $iss The issuer, usually the client_id
     * @param $sub The subject, usually a user_id
     * @param $aud The audience, usually the URI for the oauth server
     * @param $exp The expiration date. If the current time is greater than the exp, the JWT is invalid
     * @param $nbf The "not before" time. If the current time is less than the nbf, the JWT is invalid
     * @param $jti The "jwt token identifier", or nonce for this JWT
     *
     * @return string
     */
    public function generate($privateKey, $iss, $sub, $aud, $exp = null, $nbf = null, $jti = null)
    {
        if (!class_exists('OAuth2\Encryption\Jwt')) {
            throw new Exception('bshaffer/oauth2-server-php is required to generate a JWT');
        }

        if (!$exp) {
            $exp = time() + 300;
        }

        $params = array(
            'iss' => $iss,
            'sub' => $sub,
            'aud' => $aud,
            'exp' => $exp,
            'iat' => time(),
        );

        if ($nbf) {
            $params['nbf'] = $nbf;
        }

        if ($jti) {
            $params['jti'] = $jti;
        }

        $jwtUtil = new ServerJwt();

        return $jwtUtil->encode($params, $privateKey, 'RS256');
    }
}
