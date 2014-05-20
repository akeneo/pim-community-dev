<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;

/**
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductPriceNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return $object->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductPrice && 'proposal' === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        foreach ($data as $currency => $value) {
            $context['instance'][$currency]->setData($value);
        }

        return $context['instance'];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && 'pim_catalog_price_collection' === $type && 'proposal' === $format;
    }
}
