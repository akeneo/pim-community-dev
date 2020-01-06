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
    /** @var string */
    private $code;

    /** @var string */
    private $label;

    /** @var string */
    private $flowType;

    /** @var ?string */
    private $image;

    /** @var string */
    private $userRoleId;

    /** @var ?string */
    private $userGroupId;

    public function __construct(
        string $code,
        string $label,
        string $flowType,
        ?string $image,
        string $userRoleId,
        ?string $userGroupId
    ) {
        $this->code = $code;
        $this->label = $label;
        $this->flowType = $flowType;
        $this->image = $image;
        $this->userRoleId = $userRoleId;
        $this->userGroupId = $userGroupId;
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
}
