<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Command;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateAppCommand
{
    private $code;
    private $label;
    private $flowType;

    public function __construct(string $code, string $label, string $flowType)
    {
        $this->code = $code;
        $this->label = $label;
        $this->flowType = $flowType;
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
}
