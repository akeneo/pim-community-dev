<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for a collection of product values
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValuesNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var string */
    protected $valueClass;

    /** @var string[] */
    protected $supportedFormats = ['json'];

    /**
     * @param NormalizerInterface $normalizer
     * @param string              $valueClass
     */
    public function __construct(NormalizerInterface $normalizer, $valueClass)
    {
        $this->normalizer = $normalizer;
        $this->valueClass = $valueClass;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($data, $format = null, array $context = [])
    {
        $result = [];

        foreach ($data as $value) {
            $result[$value->getAttribute()->getCode()][] = $this->normalizer->normalize($value, 'json', $context);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Collection && in_array($format, $this->supportedFormats);
    }
}
