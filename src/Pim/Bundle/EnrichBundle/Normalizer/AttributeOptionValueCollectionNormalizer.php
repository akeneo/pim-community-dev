<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;

/**
 * Attribute normalizer for private api
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionValueCollectionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var SerializerInterface $serializer */
    protected $serializer;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $normalizedItems = [];

        foreach ($object as $key => $item) {
            $normalizedItems[$item->getLocale()] = $this->serializer->normalize($item, $format, $context);
        }

        return $normalizedItems;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Collection &&
            $data->first() instanceof AttributeOptionValue &&
            $format === 'array';
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
