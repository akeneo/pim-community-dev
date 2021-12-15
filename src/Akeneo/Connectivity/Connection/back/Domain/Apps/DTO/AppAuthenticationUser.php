<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\DTO;

use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppAuthenticationUser
{
    private int $pimUserId;
    private ScopeList $consentedAuthenticationScopes;
    private string $email;
    private string $firstname;
    private string $lastname;
    private string $uuid;

    public function __construct(
        int $pimUserId,
        ScopeList $consentedAuthenticationScopes,
        string $uuid,
        string $email,
        string $firstname,
        string $lastname
    ) {
        $this->pimUserId = $pimUserId;
        $this->consentedAuthenticationScopes = $consentedAuthenticationScopes;
        $this->uuid = $uuid;
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
    }

    public function getAppUserId(): string
    {
        return md5($this->uuid);
    }

    public function getPimUserId(): int
    {
        return $this->pimUserId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getConsentedAuthenticationScopes(): ScopeList
    {
        return $this->consentedAuthenticationScopes;
    }
}
