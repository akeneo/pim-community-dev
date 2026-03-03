<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Model\Read;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionWithCredentials
{
    private ?string $password = null;

    public function __construct(
        private string $code,
        private string $label,
        private string $flowType,
        private ?string $image,
        private string $clientId,
        private string $secret,
        private string $username,
        private string $userRoleId,
        private ?string $userGroupId,
        private bool $auditable,
        private string $type
    ) {
    }

    public function code(): string
    {
        return $this->code;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function flowType(): string
    {
        return $this->flowType;
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function secret(): string
    {
        return $this->secret;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function password(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function image(): ?string
    {
        return $this->image;
    }

    public function userRoleId(): string
    {
        return $this->userRoleId;
    }

    public function userGroupId(): ?string
    {
        return $this->userGroupId;
    }

    public function auditable(): bool
    {
        return $this->auditable;
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return array{
     *  code: string,
     *  label: string,
     *  flow_type: string,
     *  image: ?string,
     *  client_id: string,
     *  secret: string,
     *  username: string,
     *  password: ?string,
     *  user_role_id: string,
     *  user_group_id: ?string,
     *  auditable: bool,
     *  type: string
     * }
     */
    public function normalize(): array
    {
        return [
            'code' => $this->code,
            'label' => $this->label,
            'flow_type' => $this->flowType,
            'image' => $this->image,
            'client_id' => $this->clientId,
            'secret' => $this->secret,
            'username' => $this->username,
            'password' => $this->password,
            'user_role_id' => $this->userRoleId,
            'user_group_id' => $this->userGroupId,
            'auditable' => $this->auditable,
            'type' => $this->type,
        ];
    }
}
