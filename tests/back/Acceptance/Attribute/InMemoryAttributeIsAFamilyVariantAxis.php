<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Attribute;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\AttributeIsAFamilyVariantAxisInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryAttributeIsAFamilyVariantAxis implements AttributeIsAFamilyVariantAxisInterface
{
    private array $familyVariantAxisMap = [];

    public function execute(string $attributeCode): bool
    {
        return $this->familyVariantAxisMap[$attributeCode] ?? false;
    }

    public function setAxisAttribute(string $attributeCode, bool $isAFamilyVariantAxis): void
    {
        $this->familyVariantAxisMap[$attributeCode] = $isAFamilyVariantAxis;
    }
}
