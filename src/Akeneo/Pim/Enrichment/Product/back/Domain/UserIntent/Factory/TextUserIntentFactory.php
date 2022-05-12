<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextUserIntentFactory implements ValueUserIntentFactory
{
    public function getSupportedAttributeTypes(): array
    {
        return ['pim_catalog_text'];
    }

    public function create(string $attributeType, string $attributeCode, mixed $data): ValueUserIntent
    {
        if (null === $data['data']) {
            return new ClearValue($attributeCode, $data['scope'], $data['locale']);
        }
        return new SetTextValue($attributeCode, $data['scope'], $data['locale'], $data['data']);
    }
}
