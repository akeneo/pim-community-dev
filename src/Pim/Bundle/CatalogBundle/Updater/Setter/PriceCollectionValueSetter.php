<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

/**
 * Sets a price collection value in many products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionValueSetter extends AbstractValueSetter
{
    /** @var CurrencyManager */
    protected $currencyManager;

    /**
     * @param ProductBuilderInterface  $productBuilder
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param CurrencyManager          $currencyManager
     * @param array                    $supportedTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        CurrencyManager $currencyManager,
        array $supportedTypes
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);
        $this->currencyManager = $currencyManager;
        $this->supportedTypes  = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        $this->checkLocaleAndScope($attribute, $locale, $scope, 'prices collection');
        $this->checkData($attribute, $data);

        foreach ($products as $product) {
            $this->setPrices($attribute, $product, $data, $locale, $scope);
        }
    }

    /**
     * Check if data are valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @return mixed
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected(
                $attribute->getCode(),
                'setter',
                'prices collection',
                gettype($data)
            );
        }

        foreach ($data as $price) {
            if (!is_array($price)) {
                throw InvalidArgumentException::arrayOfArraysExpected(
                    $attribute->getCode(),
                    'setter',
                    'prices collection',
                    gettype($data)
                );
            }

            if (!array_key_exists('data', $price)) {
                throw InvalidArgumentException::arrayKeyExpected(
                    $attribute->getCode(),
                    'data',
                    'setter',
                    'prices collection',
                    print_r($data, true)
                );
            }

            if (!array_key_exists('currency', $price)) {
                throw InvalidArgumentException::arrayKeyExpected(
                    $attribute->getCode(),
                    'currency',
                    'setter',
                    'prices collection',
                    print_r($data, true)
                );
            }

            if (!is_numeric($price['data']) && null !== $price['data']) {
                throw InvalidArgumentException::arrayNumericKeyExpected(
                    $attribute->getCode(),
                    'data',
                    'setter',
                    'prices collection',
                    gettype($price['data'])
                );
            }

            if (!in_array($price['currency'], $this->currencyManager->getActiveCodes())) {
                throw InvalidArgumentException::arrayInvalidKey(
                    $attribute->getCode(),
                    'currency',
                    'The currency does not exist',
                    'setter',
                    'prices collection',
                    $price['currency']
                );
            }
        }
    }

    /**
     * Set prices into the product value
     *
     * @param AttributeInterface $attribute
     * @param ProductInterface   $product
     * @param mixed              $data
     * @param string             $locale
     * @param string             $scope
     */
    protected function setPrices(AttributeInterface $attribute, ProductInterface $product, $data, $locale, $scope)
    {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);
        if (null === $value) {
            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        }

        foreach ($data as $price) {
            $this->productBuilder->addPriceForCurrencyWithData($value, $price['currency'], $price['data']);
        }
    }
}
