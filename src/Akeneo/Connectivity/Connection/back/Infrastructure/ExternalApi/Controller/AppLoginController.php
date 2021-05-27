<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ExternalApi\Controller;

use FOS\OAuthServerBundle\Controller\AuthorizeController;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppLoginController extends AuthorizeController
{

    public function get(Request $request): Response
    {
        try {
            $this->checkClientHash();
            return $this->authorizeAction($request);
        } catch (OAuth2ServerException $e) {
            dd($e->getHttpResponse());
        } catch (\Exception $e) {
            dd($e);
        }
    }

    private function checkClientHash()
    {
    }
}