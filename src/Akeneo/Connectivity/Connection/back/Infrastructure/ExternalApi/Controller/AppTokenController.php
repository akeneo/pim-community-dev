<?php

namespace Akeneo\Connectivity\Connection\Infrastructure\ExternalApi\Controller;

use FOS\OAuthServerBundle\Controller\TokenController;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppTokenController extends TokenController
{
    use WithCheckClientHash;
    public function post(Request $request) : Response
    {
        try {
            $this->checkClientHash();
            
            return $this->server->grantAccessToken($request);
        } catch (OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }
    }
}
