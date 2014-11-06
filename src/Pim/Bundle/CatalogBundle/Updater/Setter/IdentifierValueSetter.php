<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Updater\Util\AttributeUtility;

/**
 * Sets an identifier value in many products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierValueSetter implements SetterInterface
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
        AttributeUtility::validateLocale($attribute, $locale);
        AttributeUtility::validateScope($attribute, $scope);

        if (!is_bool($data)) {
            throw new \LogicException(sprintf('Attribute "%s" expects a identifier as data', $attribute->getCode()));
        }

        foreach ($products as $product) {
            $value = $product->getValue($attribute->getCode(), $locale, $scope);
            if (null === $value) {
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
        $types = ['pim_catalog_identifier'];

        return in_array($attribute->getAttributeType(), $types);
    }
}
