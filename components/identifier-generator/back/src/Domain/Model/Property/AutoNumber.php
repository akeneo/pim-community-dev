<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AutoNumber implements PropertyInterface
{
    public function __construct(
        private int $minimalNumber,
        private int $minDigits,
    )
    {
    }
}
