<?php

namespace Pim\Bundle\ApiBundle\Controller;

use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TokenController
{
    /** @var OAuth2 */
    protected $oauthServer;

    /**
     * @param OAuth2 $oauthServer
     */
    public function __construct(OAuth2 $oauthServer)
    {
        $this->oauthServer = $oauthServer;
    }

    /**
     * @param Request $request
     *
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     */
    public function tokenAction(Request $request)
    {
        try {
            return $this->oauthServer->grantAccessToken($request);
        } catch (OAuth2ServerException $e) {
            $message = $this->getErrorMessage($e->getMessage());

            throw new UnprocessableEntityHttpException(null !== $message ? $message : $e->getDescription());
        }
    }

    /**
     * Wraps the mapping between FOS OAuth server error messages (which are actually kind of codes) and our messages.
     *
     * @param string $errorCode
     *
     * @return string|null
     */
    protected function getErrorMessage($errorCode)
    {
        $messages = [
            OAuth2::ERROR_INVALID_REQUEST     => 'Parameter "grant_type", "username" or "password" is missing or empty',
            OAuth2::ERROR_INVALID_CLIENT      => 'Parameter "client_id" is missing or does not match any client, or secret is invalid',
            OAuth2::ERROR_UNAUTHORIZED_CLIENT => 'This grant type is not authorized for this client',
            OAuth2::ERROR_INVALID_GRANT       => 'No user found for the given username and password',
        ];

        return isset($messages[$errorCode]) ? $messages[$errorCode] : null;
    }
}
