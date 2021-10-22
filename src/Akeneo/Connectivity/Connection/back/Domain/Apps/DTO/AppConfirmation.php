<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\DTO;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppConfirmation
{
    private string $appId;
    private int $userId;
    private string $userGroup;
    private int $fosClientId;

    private function __construct()
    {
    }

    public static function create(
        string $appId,
        int $userId,
        string $userGroup,
        int $fosClientId
    ): self {
        $self = new self();
        $self->appId = $appId;
        $self->userId = $userId;
        $self->userGroup = $userGroup;
        $self->fosClientId = $fosClientId;

        return $self;
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUserGroup(): string
    {
        return $this->userGroup;
    }

    public function getFosClientId(): int
    {
        return $this->fosClientId;
    }

    /**
     * @return array{
     *     app_id: string,
     *     user_id: int,
     *     user_group: string,
     *     fos_client_id: int,
     * }
     */
    public function normalize(): array
    {
        return [
            'app_id' => $this->appId,
            'user_id' => $this->userId,
            'user_group' => $this->userGroup,
            'fos_client_id' => $this->fosClientId,
        ];
    }
}
