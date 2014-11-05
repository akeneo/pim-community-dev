<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Updater\InvalidArgumentException;
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
            throw InvalidArgumentException::arrayExpected($attribute->getCode(), 'setter', 'prices collection');
        }

        foreach ($data as $price) {
            if (!is_array($price)) {
                throw InvalidArgumentException::arrayOfArraysExpected(
                    $attribute->getCode(),
                    'setter',
                    'prices collection'
                );
            }

            if (!array_key_exists('data', $price)) {
                throw InvalidArgumentException::arrayKeyExpected(
                    $attribute->getCode(),
                    'data',
                    'setter',
                    'prices collection'
                );
            }

            if (!array_key_exists('currency', $price)) {
                throw InvalidArgumentException::arrayKeyExpected(
                    $attribute->getCode(),
                    'currency',
                    'setter',
                    'prices collection'
                );
            }

            if (!is_numeric($price['data'])) {
                throw InvalidArgumentException::arrayNumericKeyExpected(
                    $attribute->getCode(),
                    'data',
                    'setter',
                    'prices collection'
                );
            }

            if (!in_array($price['currency'], $this->currencyManager->getActiveCodes())) {
                throw InvalidArgumentException::arrayInvalidKey(
                    $attribute->getCode(),
                    'currency',
                    sprintf('Currency "%s" does not exist', $price['currency']),
                    'setter',
                    'prices collection'
                );
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
