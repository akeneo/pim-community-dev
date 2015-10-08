<?php

namespace Oro\Bundle\UserBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Doctrine\Common\Cache\Cache;

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

    /**
     * Constructor.
     *
     * @param UserProviderInterface    $userProvider              An UserProviderInterface instance
     * @param PasswordEncoderInterface $encoder                   A PasswordEncoderInterface instance
     * @param Cache                    $nonceCache                The nonce cache
     * @param int                      $lifetime                  The lifetime
     * @param string                   $dateFormat                The date format
     */
    public function __construct(
        UserProviderInterface $userProvider,
        $providerKey,
        PasswordEncoderInterface $encoder,
        Cache $nonceCache,
        $lifetime=300,
        $dateFormat='/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/'
    )
    {
        if(empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        parent::__construct($userProvider, $providerKey, $encoder, $nonceCache, $lifetime, $dateFormat);

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
                $user->getApi()->getApiKey(),
                $user->getSalt()
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
