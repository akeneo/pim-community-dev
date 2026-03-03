<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ValueUserIntentFactory
{
    /**
     * @return string[]
     */
    public function getSupportedAttributeTypes(): array;

    public function create(string $attributeType, string $attributeCode, mixed $data): ValueUserIntent;
}
