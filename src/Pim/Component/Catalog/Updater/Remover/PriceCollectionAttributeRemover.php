<?php

namespace Pim\Component\Catalog\Updater\Remover;

use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Removes a price attribute (a currency) from a product
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionAttributeRemover extends AbstractAttributeRemover
{
    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /**
     * @param AttributeValidatorHelper    $attrValidatorHelper
     * @param CurrencyRepositoryInterface $currencyRepository
     * @param array                       $supportedTypes
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        CurrencyRepositoryInterface $currencyRepository,
        array $supportedTypes
    ) {
        parent::__construct($attrValidatorHelper);

        $this->currencyRepository = $currencyRepository;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format:
     * [
     *     {
     *         "amount": "12.0"|12|null,
     *         "currency": "EUR"
     *     },
     *     {
     *         "amount": "12.0"|12|null,
     *         "currency": "EUR"
     *     }
     * ]
     * "data" index is not used so it can be null
     */
    public function removeAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $this->checkLocaleAndScope($attribute, $options['locale'], $options['scope'], 'prices collection');
        $this->checkData($attribute, $data);

        $this->removePrices($product, $attribute, $data, $options['locale'], $options['scope']);
    }

    /**
     * Remove prices from product value
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param mixed              $data
     * @param string             $locale
     * @param string             $scope
     */
    protected function removePrices(ProductInterface $product, AttributeInterface $attribute, $data, $locale, $scope)
    {
        $productValue = $product->getValue($attribute->getCode(), $locale, $scope);

        if (null !== $productValue) {
            foreach ($data as $price) {
                $priceToRemove = $productValue->getPrice($price['currency']);
                $productValue->removePrice($priceToRemove);
            }
        }
    }

    /**
     * Check if data are valid
     * "data": doesn't need value
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
                'remover',
                'prices collection',
                gettype($data)
            );
        }

        foreach ($data as $price) {
            if (!is_array($price)) {
                throw InvalidArgumentException::arrayOfArraysExpected(
                    $attribute->getCode(),
                    'remover',
                    'prices collection',
                    gettype($data)
                );
            }

            if (!array_key_exists('amount', $price)) {
                throw InvalidArgumentException::arrayKeyExpected(
                    $attribute->getCode(),
                    'amount',
                    'remover',
                    'prices collection',
                    print_r($data, true)
                );
            }

            if (!array_key_exists('currency', $price)) {
                throw InvalidArgumentException::arrayKeyExpected(
                    $attribute->getCode(),
                    'currency',
                    'remover',
                    'prices collection',
                    print_r($data, true)
                );
            }

            if (!in_array($price['currency'], $this->currencyRepository->getActivatedCurrencyCodes())) {
                throw InvalidArgumentException::arrayInvalidKey(
                    $attribute->getCode(),
                    'currency',
                    'The currency does not exist',
                    'remover',
                    'prices collection',
                    $price['currency']
                );
            }
        }
    }
}
