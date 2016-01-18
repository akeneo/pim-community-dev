<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
    /** @var ProductBuilder */
    protected $productBuilder;

    /**
     * @param string[]       $supportedTypes
     * @param ProductBuilder $productBuilder
     */
    public function __construct(array $supportedTypes, ProductBuilder $productBuilder)
    {
        parent::__construct($supportedTypes);

        $this->productBuilder = $productBuilder;
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

        $value  = $context['value'];
        $prices = $this->extractPrices($data, $context);
        foreach ($prices as $price) {
            $this->addPriceForCurrency($value, $price['data'], $price['currency']);
        }

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
        $prices = [];
        $matches = [];
        $singleFieldPattern = '/(?P<data>\d+(.\d+)?) (?P<currency>\w+)/';

        if (preg_match_all($singleFieldPattern, $pricesData, $matches) === 0) {
            $prices[] = ['data' => $pricesData, 'currency' => $context['price_currency']];
        } else {
            foreach ($matches['data'] as $indData => $data) {
                $prices[] = ['data' => $data, 'currency' => $matches['currency'][$indData]];
            }
        }

        return $prices;
    }

    /**
     * @param ProductValueInterface $value
     * @param mixed                 $data
     * @param string                $currency
     */
    protected function addPriceForCurrency(ProductValueInterface $value, $data, $currency)
    {
        $priceValue = $this->productBuilder->addPriceForCurrency($value, $currency);
        $priceValue->setCurrency($currency);
        $priceValue->setData($data);
        $value->addPrice($priceValue);
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
