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
    private $code;
    private $label;
    private $flowType;
    private $auditable;

    public function __construct(string $code, string $label, string $flowType, bool $auditable = false)
    {
        $this->code = $code;
        $this->label = $label;
        $this->flowType = $flowType;
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

    public function auditable(): bool
    {
        return $this->auditable;
    }
}
