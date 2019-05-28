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

    public function __construct(string $attributeCode, string $attributeType, array $attributeProperties, bool $isLocalizable, bool $isScopable, ?string $metricFamily)
    {
        $this->attributeCode = $attributeCode;
        $this->attributeType = $attributeType;
        $this->attributeProperties = $attributeProperties;
        $this->isLocalizable = $isLocalizable;
        $this->isScopable = $isScopable;
        $this->metricFamily = $metricFamily;
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
}
