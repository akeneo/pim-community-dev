<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class Attribute
{
    /** @var string */
    private $attributeCode;

    /** @var string */
    private $attributeType;

    /** @var array */
    private $attributeProperties;

    /** @var bool */
    private $isLocalizable;

    /** @var bool */
    private $isScopable;

    /** @var ?string */
    private $metricFamily;

    /** @var null|bool */
    private $decimalsAllowed;

    /** @var string */
    private $backendType;

    /** @var string[] */
    private $availableLocaleCodes;

    public function __construct(
        string $attributeCode,
        string $attributeType,
        array $attributeProperties,
        bool $isLocalizable,
        bool $isScopable,
        ?string $metricFamily,
        ?bool $decimalsAllowed,
        string $backendType,
        array $availableLocaleCodes
    ) {
        $this->attributeCode = $attributeCode;
        $this->attributeType = $attributeType;
        $this->attributeProperties = $attributeProperties;
        $this->isLocalizable = $isLocalizable;
        $this->isScopable = $isScopable;
        $this->metricFamily = $metricFamily;
        $this->decimalsAllowed = $decimalsAllowed;
        $this->backendType = $backendType;
        $this->availableLocaleCodes = $availableLocaleCodes;
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
}
