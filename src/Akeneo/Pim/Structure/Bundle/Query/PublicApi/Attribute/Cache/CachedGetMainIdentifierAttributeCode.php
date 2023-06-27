<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Cache;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetMainIdentifierAttributeCode;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CachedGetMainIdentifierAttributeCode implements GetMainIdentifierAttributeCode, CachedQueryInterface
{
    private ?string $cachedMainIdentifierAttributeCode = null;

    public function __construct(
        private readonly GetMainIdentifierAttributeCode $getMainIdentifierAttributeCode
    ) {
    }

    public function __invoke(): string
    {
        if (null === $this->cachedMainIdentifierAttributeCode) {
            $this->cachedMainIdentifierAttributeCode = ($this->getMainIdentifierAttributeCode)();
        }

        return $this->cachedMainIdentifierAttributeCode;
    }

    public function clearCache(): void
    {
        $this->cachedMainIdentifierAttributeCode = null;
    }
}
