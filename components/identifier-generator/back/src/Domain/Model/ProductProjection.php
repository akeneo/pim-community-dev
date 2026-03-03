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
     * @param bool $enabled
     * @param string|null $familyCode
     * @param array<string, mixed> $productValues
     * Example of $productValues: [
     *   'color-<all_channels>-<all_locales>' => 'red',
     *   'description-ecommerce-en_US' => 'My description',
     *   'colors-ecommerce-<all_locales>' => ['blue', 'green'],
     * ]
     * @param array<string> $categoryCodes
     */
    public function __construct(
        private readonly bool $enabled,
        private readonly ?string $familyCode,
        private readonly array $productValues,
        private readonly array $categoryCodes,
    ) {
        Assert::isMap($productValues);
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function familyCode(): ?string
    {
        return $this->familyCode;
    }

    /**
     * @return array<string>
     */
    public function categoryCodes(): array
    {
        return $this->categoryCodes;
    }

    public function value(string $attributeCode, ?string $localeCode = null, ?string $channelCode = null): mixed
    {
        $key = \sprintf(
            '%s-%s-%s',
            $attributeCode,
            $channelCode ?? '<all_channels>',
            $localeCode ?? '<all_locales>',
        );

        return $this->productValues[$key] ?? null;
    }
}
