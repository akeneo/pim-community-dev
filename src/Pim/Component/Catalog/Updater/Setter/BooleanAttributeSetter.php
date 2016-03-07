<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Sets a boolean value in many products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanAttributeSetter extends AbstractAttributeSetter
{
    /**
     * @param ProductBuilderInterface  $productBuilder
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param array                    $supportedTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedTypes
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $this->checkLocaleAndScope($attribute, $options['locale'], $options['scope'], 'boolean');

        $this->setData($product, $attribute, $data, $options['locale'], $options['scope']);
    }

    /**
     * Set the data into the product value
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param mixed              $data
     * @param string             $locale
     * @param string             $scope
     */
    protected function setData(ProductInterface $product, AttributeInterface $attribute, $data, $locale, $scope)
    {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);

        if (null === $value) {
            if (!$this->shouldBeSetInProduct($product, $attribute, $data)) {
                return;
            }

            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        }

        if (1 === $data || '1' === $data || 0 === $data || '0' === $data) {
            $data = (bool) $data;
        }

        $value->setData($data);
    }

    /**
     * As boolean attribute does not support "null" value (only true/false),
     * there are some problems to make out what should really be added to the product (specially with optional attributes)
     * What we add in product:
     * | old value | new value | attribute is in family | should be added in product |
     * | null      | false     | yes                    | no                         |
     * | null      | false     | no                     | yes                        |
     *
     * This method should be removed when boolean attribute will be rework to support 3 states: true/false/null
     *
     * @deprecated will be removed in 1.6
     *
     * @param ProductInterface   $product    product to update
     * @param AttributeInterface $attribute  attribute
     * @param mixed              $data       new value
     *
     * @return bool
     */
    private function shouldBeSetInProduct(ProductInterface $product, AttributeInterface $attribute, $data)
    {
        $family = $product->getFamily();
        if (null !== $family && in_array($attribute->getCode(), $product->getFamily()->getAttributeCodes()) &&
            false === $data) {
            return false;
        }

        return true;
    }
}
