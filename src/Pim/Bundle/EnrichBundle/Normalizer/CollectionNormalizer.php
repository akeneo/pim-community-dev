<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Collection normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: spec it !!
 */
class CollectionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var SerializerInterface */
    //TODO: we need a normalizer here
    protected $serializer;

    /** @var array */
    protected $supportedFormat = ['internal_api'];

    //TODO: no constructor?

    /**
     * {@inheritdoc}
     */
    public function normalize($elements, $format = null, array $context = array())
    {
        $normalizedElements = [];

        foreach ($elements as $key => $element) {
            $normalizedElements[$key] = $this->serializer->normalize($element, $format, $context);
        }

        return $normalizedElements;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        //TODO \Iterable does not exist ^^
        return ($data instanceof \Iterable || is_array($data)) && in_array($format, $this->supportedFormat);
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
