<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Updater\Util\AttributeUtility;

/**
 * Copy a price collection value attribute in other price collection value attribute
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionValueCopier extends AbstractValueCopier
{
    /** @var ProductManager */
    protected $productManager;

    /**
     * @param ProductBuilder $builder
     * @param ProductManager $productManager
     * @param array          $supportedTypes
     */
    public function __construct(ProductBuilder $builder, ProductManager $productManager, array $supportedTypes)
    {
        parent::__construct(
            $builder,
            $supportedTypes
        );
        $this->productManager = $productManager;
    }

    /**
    /**
     * {@inheritdoc}
     */
    public function copyValue(
        array $products,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale = null,
        $toLocale = null,
        $fromScope = null,
        $toScope = null
    ) {
        AttributeUtility::validateLocale($fromAttribute, $fromLocale);
        AttributeUtility::validateScope($fromAttribute, $fromScope);
        AttributeUtility::validateLocale($toAttribute, $toLocale);
        AttributeUtility::validateScope($toAttribute, $toScope);

        foreach ($products as $product) {
            $fromValue = $product->getValue($fromAttribute->getCode(), $fromLocale, $fromScope);
            $fromData = (null === $fromValue) ? '' : $fromValue->getData();
            $toValue = $product->getValue($toAttribute->getCode(), $toLocale, $toScope);
            if (null === $toValue) {
                $toValue = $this->productBuilder->addProductValue($product, $toAttribute, $toLocale, $toScope);
            }

            if (is_object($fromData)) {
                foreach ($fromData as $price) {
                    $this->productBuilder
                        ->addPriceForCurrencyWithData($toValue, $price->getCurrency(), $price->getData());
                }
            }
        }
    }
}
