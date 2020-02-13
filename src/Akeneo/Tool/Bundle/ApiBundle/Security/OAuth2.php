<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\Security;

use Akeneo\Tool\Bundle\ApiBundle\EventSubscriber\ApiAuthenticationEvent;
use OAuth2\IOAuth2Storage;
use OAuth2\Model\IOAuth2AccessToken;
use OAuth2\OAuth2 as BaseOAuth2;
use OAuth2\OAuth2AuthenticateException;
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
            return parent::verifyAccessToken($tokenParam, $scope);
        } catch (OAuth2AuthenticateException $e) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, $e->getDescription(), $e);
        }
    }

    /**
     * @param Request|null $request
     *
     * @return Response
     *
     * @throws \OAuth2\OAuth2ServerException
     */
    public function grantAccessToken(Request $request = null): Response
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
        $clientId = substr($clientCredentials[0], 0, strpos($clientCredentials[0], '_'));

        $this->eventDispatcher->dispatch(
            new ApiAuthenticationEvent($request->get('username'), $clientId)
        );

        return $response;
    }
}
