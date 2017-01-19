<?php

namespace Pim\Component\Api\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Simple Collection normalizer
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SimpleCollectionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var Serializer $serializer */
    protected $serializer;

    /** @var array */
    protected $supportedFormat = ['external_api'];

    /**
     * {@inheritdoc}
     */
    public function normalize($elements, $format = null, array $context = [])
    {
        $normalizedElements = [];

        foreach ($elements as $element) {
            $normalizedElements[] = $this->serializer->normalize($element, 'standard', $context);
        }

        return $normalizedElements;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return ($data instanceof \Traversable || is_array($data)) && in_array($format, $this->supportedFormat);
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
