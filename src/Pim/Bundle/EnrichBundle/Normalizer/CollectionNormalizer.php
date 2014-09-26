<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Attribute normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CollectionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    protected $serializer;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $normalizedItems = [];

        foreach ($object as $item) {
            $normalizedItems[] = $this->serializer->normalize($item, $format, $context);
        }

        return $normalizedItems;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Collection && $format === 'array';
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
