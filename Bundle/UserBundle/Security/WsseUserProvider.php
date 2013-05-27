<?php

namespace Oro\Bundle\UserBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Escape\WSSEAuthenticationBundle\Security\Core\Authentication\Provider\Provider;
use Escape\WSSEAuthenticationBundle\Security\Core\Authentication\Token\Token;

class WsseUserProvider extends Provider
{
    /**
     * Need to override parent's "private" declaration
     *
     * @var UserProviderInterface
     */
    protected $userProvider;

    public function __construct(UserProviderInterface $userProvider, $nonceDir = null, $lifetime = 300)
    {
        parent::__construct($userProvider, $nonceDir, $lifetime);

        $this->userProvider = $userProvider;
    }

    /**
     * Authenticate API user by API key
     *
     * @param  TokenInterface          $token
     * @return Token
     * @throws AuthenticationException
     */
    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        if ($user && $user->getApi()) {
            if ($this->validateDigest(
                $token->getAttribute('digest'),
                $token->getAttribute('nonce'),
                $token->getAttribute('created'),
                $user->getApi()->getApiKey()
            )) {
                $authToken = new Token($user->getRoles());

                $authToken->setUser($user);
                $authToken->setAuthenticated(true);

                return $authToken;
            }
        }

        throw new AuthenticationException('WSSE authentication failed.');
    }
}
