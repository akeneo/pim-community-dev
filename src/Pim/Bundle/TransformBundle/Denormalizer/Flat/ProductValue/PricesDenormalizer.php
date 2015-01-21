<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if ($data === null || $data === '') {
            return null;
        }

        $resolver = new OptionsResolver();
        $this->configContext($resolver);
        $context = $resolver->resolve($context);

        $value    = $context['value'];
        $currency = $context['price_currency'];

        $priceValue = $this->productBuilder->addPriceForCurrency($value, $currency);
        $priceValue->setCurrency($currency);
        $priceValue->setData($data);

        return $priceValue;
    }

    /**
     * Define context requirements
     * @param OptionsResolverInterface $resolver
     */
    protected function configContext(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(['value', 'price_currency'])
            ->setOptional(['entity', 'locale_code', 'product', 'scope_code']);
    }
}
