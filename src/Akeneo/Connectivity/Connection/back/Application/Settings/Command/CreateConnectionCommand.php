<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Command;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateConnectionCommand
{
    private string $code;

    private string $label;

    private string $flowType;

    private bool $auditable;

    private ?string $type;

    private ?string $userGroup;

    public function __construct(
        string $code,
        string $label,
        string $flowType,
        bool $auditable = false,
        ?string $type = null,
        ?string $userGroup = null,
    ) {
        $this->code = $code;
        $this->label = $label;
        $this->flowType = $flowType;
        $this->auditable = $auditable;
        $this->type = $type;
        $this->userGroup = $userGroup;
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

    public function auditable(): bool
    {
        return $this->auditable;
    }

    public function type(): ?string
    {
        return $this->type;
    }

    public function userGroup(): ?string
    {
        return $this->userGroup;
    }
}
