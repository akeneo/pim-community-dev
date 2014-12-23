<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Updater\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

/**
 * Abstract copier
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractValueCopier implements CopierInterface
{
    /** @var array */
    protected $supportedTypes = [];

    /** @var ProductBuilder */
    protected $productBuilder;

    /** @var AttributeValidatorHelper */
    protected $attributeValidatorHelper;

    /**
     * @param ProductBuilderInterface  $productBuilder
     * @param AttributeValidatorHelper $attributeValidatorHelper
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attributeValidatorHelper
    ) {
        $this->productBuilder           = $productBuilder;
        $this->attributeValidatorHelper = $attributeValidatorHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(AttributeInterface $fromAttribute, AttributeInterface $toAttribute)
    {
        $supportsFrom = in_array($fromAttribute->getAttributeType(), $this->supportedTypes);
        $supportsTo   = in_array($toAttribute->getAttributeType(), $this->supportedTypes);

        return $supportsFrom && $supportsTo;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedTypes()
    {
        return $this->supportedTypes;
    }

    /**
     * Check locale and scope are valid
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     * @param string             $type
     *
     * @throws InvalidArgumentException
     */
    protected function checkLocaleAndScope(AttributeInterface $attribute, $locale, $scope, $type)
    {
        try {
            $this->attributeValidatorHelper->validateLocale($attribute, $locale);
            $this->attributeValidatorHelper->validateScope($attribute, $scope);
        } catch (\LogicException $e) {
            throw InvalidArgumentException::expectedFromPreviousException(
                $e,
                $attribute->getCode(),
                'copier',
                $type
            );
        }
    }

    /**
     * Check that unit families of 2 attributes are consistent.
     *
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     * @param string             $type
     *
     * @throws InvalidArgumentException
     */
    protected function checkUnitFamily(
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $type
    ) {
        try {
            $this->attributeValidatorHelper->validateUnitFamilies($fromAttribute, $toAttribute);
        } catch (\LogicException $e) {
            throw InvalidArgumentException::expectedFromPreviousException(
                $e,
                $fromAttribute->getCode() . ' && ' . $toAttribute->getCode(),
                'copier',
                $type
            );
        }
    }
}
