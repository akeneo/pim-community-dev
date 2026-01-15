<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\Security;

use Akeneo\Tool\Component\Api\Event\ApiAuthenticationEvent;
use Akeneo\Tool\Component\Api\Event\ApiAuthenticationFailedEvent;
use Akeneo\UserManagement\Component\Model\User;
use OAuth2\IOAuth2Storage;
use OAuth2\Model\IOAuth2AccessToken;
use OAuth2\OAuth2 as BaseOAuth2;
use OAuth2\OAuth2AuthenticateException;
use OAuth2\OAuth2ServerException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OAuth2 extends BaseOAuth2
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(IOAuth2Storage $storage, EventDispatcherInterface $eventDispatcher, $config = [])
    {
        parent::__construct($storage, $config);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function verifyAccessToken($tokenParam, $scope = null): IOAuth2AccessToken
    {
        try {
            $accessToken = parent::verifyAccessToken($tokenParam, $scope);
            $data = $accessToken->getData();

            if ($data instanceof User) {
                $this->eventDispatcher->dispatch(
                    new ApiAuthenticationEvent(
                        $data->getUserIdentifier(),
                        $this->getClientIdFromPublicId($accessToken->getClientId())
                    )
                );
            }

            return $accessToken;
        } catch (OAuth2AuthenticateException $e) {
            $this->eventDispatcher->dispatch(new ApiAuthenticationFailedEvent($e, $tokenParam));

            throw new HttpException(Response::HTTP_UNAUTHORIZED, $e->getDescription(), $e);
        }
    }

    /**
     * @param Request|null $request
     *
     * @return Response
     *
     * @throws OAuth2ServerException
     */
    public function grantAccessToken(?Request $request = null): Response
    {
        $response = parent::grantAccessToken($request);

        if (null === $request->get('username') || '' === $request->get('username')) {
            return $response;
        }

        if ($request->getMethod() === 'POST') {
            $inputData = $request->request->all();
        } else {
            $inputData = $request->query->all();
        }

        $authHeaders = $this->getAuthorizationHeader($request);
        $clientCredentials = $this->getClientCredentials($inputData, $authHeaders);

        $this->eventDispatcher->dispatch(
            new ApiAuthenticationEvent(
                $request->get('username'),
                $this->getClientIdFromPublicId($clientCredentials[0])
            )
        );

        return $response;
    }

    private function getClientIdFromPublicId(string $publicId): string
    {
        return substr($publicId, 0, strpos($publicId, '_'));
    }
}
