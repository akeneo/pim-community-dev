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
    public function __construct(
        private string $code,
        private string $label,
        private string $flowType,
        private bool $auditable = false,
        private ?string $type = null,
        private ?string $userGroup = null,
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
