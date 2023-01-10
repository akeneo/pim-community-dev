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
     * @param array<string, mixed> $productValues
     * Example of $productValues: [
     *   'color-<all_channels>-<all_locales>' => 'red',
     *   'description-ecommerce-en_US' => 'My description',
     *   'colors-ecommerce-<all_locales>' => ['blue', 'green'],
     * ]
     */
    public function __construct(
        private readonly ?string $identifier,
        private readonly bool $enabled,
        private readonly ?string $familyCode,
        private readonly array $productValues,
    ) {
        Assert::isMap($productValues);
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

    public function value(string $attributeCode, ?string $localeCode, ?string $channelCode): mixed
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
