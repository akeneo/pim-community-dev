<?php

namespace Pim\Component\Catalog\Updater\Adder;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\PriceCollectionInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Price collection attribute adder
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionAttributeAdder extends AbstractAttributeAdder
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param ProductBuilderInterface     $productBuilder
     * @param NormalizerInterface         $normalizer
     * @param array                       $supportedTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        NormalizerInterface $normalizer,
        array $supportedTypes
    ) {
        parent::__construct($productBuilder);

        $this->normalizer = $normalizer;
        $this->supportedTypes = $supportedTypes;
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
    public function addAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);

        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        $this->addPrices($product, $attribute, $data, $options['locale'], $options['scope']);
    }

    /**
     * Add prices into the product value
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param mixed              $data
     * @param string             $locale
     * @param string             $scope
     */
    protected function addPrices(ProductInterface $product, AttributeInterface $attribute, $data, $locale, $scope)
    {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);
        if (null !== $value) {
            $data = $this->addNewPrices($value->getData(), $data);
        }

        $this->productBuilder->addOrReplaceProductValue($product, $attribute, $locale, $scope, $data);
    }

    /**
     * Returns the combination of the previous product prices and the new prices
     * to add, all of them in PIM standard format.
     *
     * It is possible to have several prices for the same currency, this will be
     * handled by the ProductValueFactory which will keep only the last one
     * (here, it will be the new one passed to the adder).
     *
     * Validation will also be performed by the factory (array correctly
     * formatted, locale, scope...).
     *
     * @param PriceCollectionInterface $previousPrices
     * @param array                    $newPrices
     *
     * @return array
     */
    protected function addNewPrices(PriceCollectionInterface $previousPrices, array $newPrices)
    {
        $standardizedPreviousPrices = [];

        foreach ($previousPrices as $previousPrice) {
            $standardizedPreviousPrices[] = $this->normalizer->normalize($previousPrice, 'standard');
        }

        return array_merge($standardizedPreviousPrices, $newPrices);
    }
}
