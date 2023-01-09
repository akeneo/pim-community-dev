<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductProjection
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(
        private readonly ?string $identifier,
        private readonly bool $enabled,
        private readonly ?string $familyCode,
        private readonly array $productValues,
    ) {
        Assert::isMap($productValues);
        foreach($productValues as $_attributeCode => $attributeValues) {
            Assert::isMap($attributeValues);
        }
    }

    public function identifier(): ?string
    {
        return $this->identifier;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function familyCode(): ?string
    {
        return $this->familyCode;
    }

    public function value(string $attributeCode, ?string $locale = null, ?string $scope = null): mixed
    {
        $key = \join('_', [$locale ?? '<all_locales>', $scope ?? '<all_channels>']);

        return $this->productValues[$attributeCode][$key] ?? null;
    }
}
