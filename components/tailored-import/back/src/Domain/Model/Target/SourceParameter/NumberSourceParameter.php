<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberSourceParameter implements SourceParameterInterface
{
    public function __construct(
        private string $decimalSeparator,
    ) {
    }

    public function getDecimalSeparator(): string
    {
        return $this->decimalSeparator;
    }
}
