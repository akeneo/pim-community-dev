<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Command;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class UpdateConnectionCommand
{
    private string $code;

    private string $label;

    private string $flowType;

    private ?string $image;

    private string $userRoleId;

    private ?string $userGroupId;

    private bool $auditable;

    public function __construct(
        string $code,
        string $label,
        string $flowType,
        ?string $image,
        string $userRoleId,
        ?string $userGroupId,
        bool $auditable
    ) {
        $this->code = $code;
        $this->label = $label;
        $this->flowType = $flowType;
        $this->image = $image;
        $this->userRoleId = $userRoleId;
        $this->userGroupId = $userGroupId;
        $this->auditable = $auditable;
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
}
