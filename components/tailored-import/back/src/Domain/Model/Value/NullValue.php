<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\Model\Value;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NullValue implements ValueInterface
{
    public function getValue(): mixed
    {
        return null;
    }
}
