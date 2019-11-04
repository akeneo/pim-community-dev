<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\Storage;

use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    protected $stdNormalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->stdNormalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attributeOption, $format = null, array $context = [])
    {
        return $this->stdNormalizer->normalize($attributeOption, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeOptionInterface && 'storage' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
