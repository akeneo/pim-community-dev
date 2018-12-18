<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AuthenticationProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Authentication\AuthenticationWebService;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AuthenticationProvider implements AuthenticationProviderInterface
{
    /** @var AuthenticationWebService */
    private $authenticationApi;

    /**
     * @param AuthenticationWebService $authenticationApi
     */
    public function __construct(AuthenticationWebService $authenticationApi)
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
