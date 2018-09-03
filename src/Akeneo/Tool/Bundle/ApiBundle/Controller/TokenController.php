<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Controller;

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
            $grantType = $request->request->get('grant_type');
            $message = $this->getErrorMessage($e->getMessage(), $grantType);

            throw new UnprocessableEntityHttpException(null !== $message ? $message : $e->getDescription());
        }
    }

    /**
     * Wraps the mapping between FOS OAuth server error messages (which are actually kind of codes) and our messages.
     *
     * @param string $errorCode
     * @param string $grantType
     *
     * @return null|string
     */
    protected function getErrorMessage($errorCode, $grantType)
    {
        $messages = [
            OAuth2::ERROR_INVALID_REQUEST     => 'Parameter "grant_type", "username" or "password" is missing, empty or invalid',
            OAuth2::ERROR_INVALID_CLIENT      => 'Parameter "client_id" is missing or does not match any client, or secret is invalid',
            OAuth2::ERROR_UNAUTHORIZED_CLIENT => 'This grant type is not authorized for this client',
            OAuth2::ERROR_INVALID_GRANT       => 'No user found for the given username and password',
        ];

        if (OAuth2::GRANT_TYPE_REFRESH_TOKEN === $grantType) {
            $messages[OAuth2::ERROR_INVALID_REQUEST] = 'Parameter "grant_type" or "refresh_token" is missing or empty';
            $messages[OAuth2::ERROR_INVALID_GRANT]   = 'Refresh token is invalid or has expired';
        }

        return isset($messages[$errorCode]) ? $messages[$errorCode] : null;
    }
}
