<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

/**
 * Abstract product value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractValue implements ValueInterface
{
    /** @var mixed */
    protected $data;

    /** @var string */
    protected $attributeCode;

    /** @var string */
    protected $localeCode;

    /** @var string */
    protected $scopeCode;

    /**
     * Forbid external access to the default constructor to force usage of named constructors
     */
    protected function __construct(string $attributeCode, $data, ?string $scopeCode, ?string $localeCode)
    {
        $this->attributeCode = $attributeCode;
        $this->data = $data;
        $this->scopeCode = $scopeCode;
        $this->localeCode = $localeCode;
    }

    /**
     * Named constructor for non scopable, non localizable value
     */
    public static function value(string $attributeCode, $data): ValueInterface
    {
        return new static($attributeCode, $data, null, null);
    }

    /**
     * Named constructor for scopable and non localizable value
     */
    public static function scopableValue(string $attributeCode, $data, string $scopeCode): ValueInterface
    {
        return new static($attributeCode, $data, $scopeCode, null);
    }

    /**
     * Named constructor for localizable, non scopable value
     */
    public static function localizableValue(string $attributeCode, $data, string $localeCode): ValueInterface
    {
        return new static($attributeCode, $data, null, $localeCode);
    }

    /**
     * Named constructor for scopable and localizable value
     */
    public static function scopableLocalizableValue(string $attributeCode, $data, string $scopeCode, string $localeCode): ValueInterface
    {
        return new static($attributeCode, $data, $scopeCode, $localeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function hasData(): bool
    {
        return !is_null($this->getData());
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocaleCode(): ?string
    {
        return $this->localeCode;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function isEqual(ValueInterface $value): bool;

    /**
     * {@inheritdoc}
     */
    public function getScopeCode(): ?string
    {
        return $this->scopeCode;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocalizable(): bool
    {
        return null !== $this->localeCode;
    }

    /**
     * {@inheritdoc}
     */
    public function isScopable(): bool
    {
        return null !== $this->scopeCode;
    }
}
