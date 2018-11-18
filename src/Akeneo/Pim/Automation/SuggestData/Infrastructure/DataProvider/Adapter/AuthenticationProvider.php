<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AuthenticationProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Authentication\AuthenticationApiInterface;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AuthenticationProvider implements AuthenticationProviderInterface
{
    /** @var AuthenticationApiInterface */
    private $authenticationApi;

    /**
     * @param AuthenticationApiInterface $authenticationApi
     */
    public function __construct(AuthenticationApiInterface $authenticationApi)
    {
        $this->authenticationApi = $authenticationApi;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(Token $token): bool
    {
        return $this->authenticationApi->authenticate((string) $token);
    }
}
