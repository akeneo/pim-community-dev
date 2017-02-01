<?php

namespace Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Price collection flat denormalizer used for attribute type:
 * - pim_catalog_price_collection
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricesDenormalizer extends AbstractValueDenormalizer
{
    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param string[]                $supportedTypes
     * @param ProductBuilderInterface $productBuilder
     * @param NormalizerInterface     $normalizer
     */
    public function __construct(
        array $supportedTypes,
        ProductBuilderInterface $productBuilder,
        NormalizerInterface $normalizer
    ) {
        parent::__construct($supportedTypes);

        $this->productBuilder = $productBuilder;
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $data = ('' === $data) ? null : $data;

        $resolver = new OptionsResolver();
        $this->configContext($resolver);
        $context = $resolver->resolve($context);

        $value = $context['value'];
        $product = $context['product'];
        $prices = $this->extractPrices($data, $context);

        $value = $this->addPriceForCurrency($product, $value, $prices);

        return $value->getPrices();
    }

    /**
     * Data can contains one price or several
     *
     * @param mixed $pricesData
     * @param array $context
     *
     * @return array
     */
    protected function extractPrices($pricesData, $context)
    {
        if (null === $pricesData) {
            return [];
        }

        $prices = [];
        $matches = [];
        $singleFieldPattern = '/(?P<data>\d+(.\d+)?) (?P<currency>\w+)/';

        if (preg_match_all($singleFieldPattern, $pricesData, $matches) === 0) {
            $prices[] = ['amount' => $pricesData, 'currency' => $context['price_currency']];
        } else {
            foreach ($matches['data'] as $indData => $data) {
                $prices[] = ['amount' => $data, 'currency' => $matches['currency'][$indData]];
            }
        }

        return $prices;
    }

    /**
     * @param ProductInterface      $product
     * @param ProductValueInterface $value
     * @param array                 $prices
     *
     * @return ProductValueInterface
     */
    protected function addPriceForCurrency(ProductInterface $product, ProductValueInterface $value, array $prices)
    {
        $originalPrices = $this->normalizer->normalize($value->getPrices(), 'standard');

        $priceValue = $this->productBuilder->addProductValue(
            $product,
            $value->getAttribute(),
            $value->getLocale(),
            $value->getScope(),
            !empty($prices) ? $this->replaceOriginalPrices($originalPrices, $prices) : null
        );

        return $priceValue;
    }

    /**
     * Replaces a price by another one for the same currency.
     *
     * @param array $originalPrices
     * @param array $prices
     *
     * @return array
     */
    protected function replaceOriginalPrices(array $originalPrices, array $prices)
    {
        foreach ($originalPrices as $key => $originalPrice) {
            foreach ($prices as $price) {
                if ($originalPrice['currency'] === $price['currency']) {
                    $originalPrices[$key]['amount'] = $price['amount'];
                }
            }
        }

        return $originalPrices;
    }

    /**
     * Define context requirements
     *
     * @param OptionsResolver $resolver
     */
    protected function configContext(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['value', 'price_currency'])
            ->setDefined(['entity', 'locale_code', 'product', 'scope_code']);
    }
}
