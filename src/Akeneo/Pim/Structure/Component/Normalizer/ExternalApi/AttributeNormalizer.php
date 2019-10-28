<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\ExternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    protected $stdNormalizer;

    /**
     * @param NormalizerInterface $stdNormalizer
     */
    public function __construct(NormalizerInterface $stdNormalizer)
    {
        $this->stdNormalizer = $stdNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        $normalizedAttribute = $this->stdNormalizer->normalize($attribute, 'standard', $context);

        if (empty($normalizedAttribute['labels'])) {
            $normalizedAttribute['labels'] = (object) $normalizedAttribute['labels'];
        }

        return $normalizedAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeInterface && 'external_api' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
