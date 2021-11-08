<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\DTO;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppDeletion
{
    private string $appId;
    private string $connectionCode;
    private string $userGroupName;
    private string $userRole;

    public function __construct(
        string $appId,
        string $connectionCode,
        string $userGroupName,
        string $userRole
    ) {
        $this->appId = $appId;
        $this->connectionCode = $connectionCode;
        $this->userGroupName = $userGroupName;
        $this->userRole = $userRole;
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getConnectionCode(): string
    {
        return $this->connectionCode;
    }

    public function getUserGroupName(): string
    {
        return $this->userGroupName;
    }

    public function getUserRole(): string
    {
        return $this->userRole;
    }
}
