<?php

namespace Pim\Component\Catalog\Model;

/**
 * Abstract product value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductValue implements ProductValueInterface
{
    /** @var AttributeInterface */
    protected $attribute;

    /** @var string Locale code */
    protected $locale;

    /** @var string Scope code */
    protected $scope;

    /**
     * {@inheritdoc}
     */
    public function hasData()
    {
        return !is_null($this->getData());
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ProductValueInterface $productValue)
    {
        return $this->getData() === $productValue->getData() &&
            $this->scope === $productValue->getScope() &&
            $this->locale === $productValue->getLocale();
    }

    /**
     * Set attribute
     *
     * @param AttributeInterface $attribute
     *
     * @throws \LogicException
     *
     * @return ProductValueInterface
     */
    protected function setAttribute(AttributeInterface $attribute = null)
    {
        if (is_object($this->attribute) && ($attribute != $this->attribute)) {
            throw new \LogicException(
                sprintf('An attribute (%s) has already been set for this value', $this->attribute->getCode())
            );
        }
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Set used scope code
     *
     * @param string $scope
     */
    protected function setScope($scope)
    {
        if ($scope && $this->getAttribute() && !$this->getAttribute()->isScopable()) {
            $attributeCode = $this->getAttribute()->getCode();
            throw new \LogicException(
                "The product value cannot be scoped, see attribute '".$attributeCode."' configuration"
            );
        }

        $this->scope = $scope;
    }

    /**
     * Set used locale code
     *
     * @param string $locale
     */
    protected function setLocale($locale)
    {
        if ($locale && $this->getAttribute() && !$this->getAttribute()->isLocalizable()) {
            $attributeCode = $this->getAttribute()->getCode();
            throw new \LogicException(
                "The product value cannot be localized, see attribute '".$attributeCode."' configuration"
            );
        }

        $this->locale = $locale;
    }
}
