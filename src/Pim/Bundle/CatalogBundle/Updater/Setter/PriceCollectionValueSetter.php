<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
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
     *
     * @deprecated will be removed in 1.5, use method setAttributeData
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        foreach ($products as $product) {
            $this->setAttributeData($product, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        }
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format:
     * [
     *     {
     *         "data": "12.0"|"12"|12|12.3,
     *         "currency": "EUR"
     *     },
     *     {
     *         "data": "12.0"|"12"|12|12.3,
     *         "currency": "EUR"
     *     }
     * ]
     */
    public function setAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $this->resolver->resolve($options);
        $this->checkLocaleAndScope($attribute, $options['locale'], $options['scope'], 'prices collection');
        $this->checkData($attribute, $data);

        $this->setPrices($product, $attribute, $data, $options['locale'], $options['scope']);
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
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param mixed              $data
     * @param string             $locale
     * @param string             $scope
     */
    protected function setPrices(ProductInterface $product, AttributeInterface $attribute, $data, $locale, $scope)
    {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);

        if (null === $value) {
            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        } else {
            $prices = $value->getPrices();
            foreach ($prices as $price) {
                $price->setData(null);
            }
        }

        foreach ($data as $price) {
            $this->productBuilder->addPriceForCurrencyWithData($value, $price['currency'], $price['data']);
        }
    }
}
