<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class Attribute
{
    public function __construct(
        private string $attributeCode,
        private string $attributeType,
        private array $attributeProperties,
        private bool $isLocalizable,
        private bool $isScopable,
        private ?string $metricFamily,
        private ?string $defaultMetricUnit,
        private ?bool $decimalsAllowed,
        private string $backendType,
        private array $availableLocaleCodes,
        private ?bool $useableAsGridFilter = null,
        private ?int $sortOrder = 0,
    ) {
    }

    public function code(): string
    {
        return $this->attributeCode;
    }

    public function type(): string
    {
        return $this->attributeType;
    }

    public function properties(): array
    {
        return $this->attributeProperties;
    }

    public function isScopable(): bool
    {
        return $this->isScopable;
    }

    public function isLocalizable(): bool
    {
        return $this->isLocalizable;
    }

    public function isLocalizableAndScopable(): bool
    {
        return $this->isLocalizable() && $this->isScopable();
    }

    public function metricFamily(): ?string
    {
        return $this->metricFamily;
    }

    public function defaultMetricUnit(): ?string
    {
        return $this->defaultMetricUnit;
    }

    public function isDecimalsAllowed(): ?bool
    {
        return $this->decimalsAllowed;
    }

    public function backendType(): string
    {
        return $this->backendType;
    }

    public function isLocaleSpecific(): bool
    {
        return !empty($this->availableLocaleCodes);
    }

    public function availableLocaleCodes(): array
    {
        return $this->availableLocaleCodes;
    }

    public function useableAsGridFilter(): ?bool
    {
        return $this->useableAsGridFilter;
    }

    public function sortOrder(): ?int
    {
        return $this->sortOrder;
    }
}
