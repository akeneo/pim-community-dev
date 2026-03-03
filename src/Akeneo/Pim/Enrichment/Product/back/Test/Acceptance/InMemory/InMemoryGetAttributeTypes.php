<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetAttributeTypes;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryGetAttributeTypes implements GetAttributeTypes
{
    /**
     * @var array<string, string>
     */
    private array $attributeTypesByCode = [];

    public function fromAttributeCodes(array $attributeCodes): array
    {
        Assert::allString($attributeCodes);

        $results = [];
        foreach ($attributeCodes as $attributeCode) {
            foreach ($this->attributeTypesByCode as $savedAttributeCode => $attributeType) {
                if (\strtolower($attributeCode) === \strtolower($savedAttributeCode)) {
                    $results[$savedAttributeCode] = $attributeType;
                }
            }
        }

        return $results;
    }

    public function saveAttribute(string $attributeCode, string $attributeType): void
    {
        $this->attributeTypesByCode[$attributeCode] = $attributeType;
    }
}
