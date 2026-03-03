<?php

declare(strict_types=1);

namespace Akeneo\Channel\API\Query;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ConversionUnitCollection
{
    /** @param array<string, string> $conversionUnits */
    private function __construct(
        private array $conversionUnits,
    ) {
        Assert::allString($conversionUnits);
    }

    /**
     * @param array<string, string> $conversionUnits
     */
    public static function fromArray(array $conversionUnits): self
    {
        return new self($conversionUnits);
    }

    public function hasConversionUnit(string $attributeCode): bool
    {
        return array_key_exists($attributeCode, $this->conversionUnits);
    }

    public function getConversionUnit(string $attributeCode): ?string
    {
        return $this->conversionUnits[$attributeCode] ?? null;
    }
}
