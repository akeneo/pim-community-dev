<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Query;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;

class IsMultiSourceTarget
{
    public function __construct(
        private array $multiSourceSystemProperties,
        private array $multiSourceAttributeTypes,
        private GetAttributes $getAttributes
    ) {
    }

    public function isAttributeMultiSourceTarget(string $attributeCode): bool
    {
        $attribute = $this->getAttributes->forCode($attributeCode);

        if (null === $attribute) {
            throw new \InvalidArgumentException(sprintf('Attribute "%s" does not exist', $attributeCode));
        }

        $attributeType = $attribute->type();

        return in_array($attributeType, $this->multiSourceAttributeTypes);
    }

    public function isSystemPropertyMultiSourceTarget(string $systemPropertyCode): bool
    {
        return in_array($systemPropertyCode, $this->multiSourceSystemProperties);
    }
}
