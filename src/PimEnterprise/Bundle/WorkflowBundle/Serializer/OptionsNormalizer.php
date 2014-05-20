<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

/**
 * Normalize/Denormalize collection of options product value
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class OptionsNormalizer extends SerializerAwareNormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $collection = $context['instance'];

        foreach ($data as $key => $id) {
            if (null !== $id) {
                $collection->add(
                    $this->serializer->denormalize($id, 'pim_catalog_simpleselect', $format, [])
                );
            } else {
                $collection->remove($key);
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && 'pim_catalog_multiselect' === $type && 'proposal' === $format;
    }
}
