<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IdentifierValue implements IdentifierValueInterface
{
    private function __construct(
        private readonly string $attributeCode,
        private readonly bool $isMainIdentifier,
        private readonly ?string $data
    ) {
    }

    public static function value(string $attributeCode, bool $isMainIdentifier, $data): self
    {
        Assert::nullOrStringNotEmpty($data);
        return new self($attributeCode, $isMainIdentifier, $data);
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }

    public function isMainIdentifier(): bool
    {
        return $this->isMainIdentifier;
    }

    public function getLocaleCode(): ?string
    {
        return null;
    }

    public function isLocalizable(): bool
    {
        return false;
    }

    public function hasData(): bool
    {
        return null !== $this->data;
    }

    public function getScopeCode(): ?string
    {
        return null;
    }

    public function isScopable(): bool
    {
        return false;
    }

    public function isEqual(ValueInterface $value): bool
    {
        return $value instanceof IdentifierValue && $this->data === $value->getData();
    }

    public function __toString(): string
    {
        return $this->data;
    }
}
