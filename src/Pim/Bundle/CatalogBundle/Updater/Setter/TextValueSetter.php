<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;

/**
 * Sets a text value in many products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextValueSetter implements SetterInterface
{
    /** @var ProductBuilder */
    protected $productBuilder;

    /**
     * @param ProductBuilder $builder
     */
    public function __construct(ProductBuilder $builder)
    {
        $this->productBuilder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        $this->validateData($attribute, $data);
        $this->validateContext($attribute, $locale, $scope);

        foreach ($products as $product) {
            $value = $product->getValue($attribute->getCode(), $locale, $scope);
            if (null === $value) {
                // TODO : not sure about the relevancy of product builder for this kind of operation
                $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
            }
            $value->setData($data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(AttributeInterface $attribute)
    {
        $types = ['pim_catalog_text', 'pim_catalog_textarea'];

        return in_array($attribute->getAttributeType(), $types);
    }

    /**
     * Validate the data
     *
     * @param AttributeInterface $attribute
     * @param string             $data
     *
     * @throws \LogicException
     */
    protected function validateData(AttributeInterface $attribute, $data)
    {
        if (!is_string($data)) {
            throw new \LogicException('Attribute "%s" expects a string data', $attribute->getCode());
        }
    }

    /**
     * Validate the context
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     *
     * @throws \LogicException
     */
    protected function validateContext(AttributeInterface $attribute, $locale, $scope)
    {
        // TODO : check the existence of locale and scope used as options
        // TODO : extract this code in a dedicated class
        if ($attribute->isLocalizable() && $locale === null) {
            throw new \LogicException(
                sprintf(
                    'Locale expected for the attribute "%s"',
                    $attribute->getCode()
                )
            );
        }
        if (!$attribute->isLocalizable() && $locale !== null) {
            throw new \LogicException(
                sprintf(
                    'Locale is not expected for the attribute "%s"',
                    $attribute->getCode()
                )
            );
        }
        if ($attribute->isScopable() && $scope === null) {
            throw new \LogicException(
                sprintf(
                    'Scope expected for the attribute "%s"',
                    $attribute->getCode()
                )
            );
        }
        if (!$attribute->isScopable() && $scope !== null) {
            throw new \LogicException(
                sprintf(
                    'Scope is not expected for the attribute "%s"',
                    $attribute->getCode()
                )
            );
        }
    }
}
