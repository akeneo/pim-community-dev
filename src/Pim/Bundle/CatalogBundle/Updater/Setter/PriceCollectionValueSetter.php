<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Updater\Util\AttributeUtility;

/**
 * Sets a price collection value in many products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionValueSetter extends AbstractValueSetter
{
    /** @var ProductBuilder */
    protected $productBuilder;

    /** @var CurrencyManager */
    protected $currencyManager;

    /** @var ProductManager */
    protected $productManager;

    /**
     * @param ProductBuilder  $builder
     * @param CurrencyManager $currencyManager
     * @param ProductManager  $productManager
     * @param array           $supportedTypes
     */
    public function __construct(
        ProductBuilder $builder,
        CurrencyManager $currencyManager,
        ProductManager $productManager,
        array $supportedTypes
    ) {
        $this->productBuilder  = $builder;
        $this->currencyManager = $currencyManager;
        $this->productManager  = $productManager;
        $this->types           = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        AttributeUtility::validateLocale($attribute, $locale);
        AttributeUtility::validateScope($attribute, $scope);

        if (!is_array($data)) {
            throw new \LogicException(
                sprintf('Attribute "%s" expects an array as data', $attribute->getCode())
            );
        }

        foreach ($data as $price) {
            if (!is_array($price)) {
                throw new \LogicException(
                    sprintf('$data should contains arrays as value', $attribute->getCode())
                );
            }

            if (!array_key_exists('data', $price)) {
                throw new \LogicException('Missing "data" key in array');
            }

            if (!array_key_exists('currency', $price)) {
                throw new \LogicException('Missing "currency" key in array');
            }

            if (!is_numeric($price['data'])) {
                throw new \LogicException('"data" should contains a numeric value');
            }

            if (!in_array($price['currency'], $this->currencyManager->getActiveCodes())) {
                throw new \LogicException('Invalid currency');
            }
        }

        foreach ($products as $product) {
            $value = $product->getValue($attribute->getCode(), $locale, $scope);
            if (null === $value) {
                $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
            }

            foreach ($data as $price) {
                $this->productBuilder->addPriceForCurrencyWithData($value, $price['currency'], $price['data']);
            }
        }
    }
}
