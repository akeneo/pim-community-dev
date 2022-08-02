<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Permission;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\IsAttributeReadable;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DummyIsAttributeReadable implements IsAttributeReadable
{
    public function forCode(string $attributeCode, int $userId): bool
    {
        return true;
    }
}
