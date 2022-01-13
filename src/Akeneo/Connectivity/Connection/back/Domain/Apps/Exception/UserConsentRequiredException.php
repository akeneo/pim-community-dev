<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\Exception;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserConsentRequiredException extends \Exception
{
    private string $appId;
    private int $pimUserId;
    /** @var string[] */
    private array $newAuthenticationScopes;

    /**
     * @param string[] $newAuthenticationScopes
     */
    public function __construct(string $appId, int $pimUserId, array $newAuthenticationScopes)
    {
        parent::__construct();

        $this->appId = $appId;
        $this->pimUserId = $pimUserId;
        $this->newAuthenticationScopes = $newAuthenticationScopes;
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getPimUserId(): int
    {
        return $this->pimUserId;
    }

    /**
     * @return string[]
     */
    public function getNewAuthenticationScopes(): array
    {
        return $this->newAuthenticationScopes;
    }
}
