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

    /** @var NormalizerInterface */
    private $translationNormalizer;

    /**
     * @param NormalizerInterface $stdNormalizer
     * @param NormalizerInterface $translationNormalizer
     */
    public function __construct(NormalizerInterface $stdNormalizer, NormalizerInterface $translationNormalizer)
    {
        $this->stdNormalizer = $stdNormalizer;
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        $normalizedAttribute = $this->stdNormalizer->normalize($attribute, 'standard', $context);

        foreach (['labels', 'descriptions'] as $field) {
            if (array_key_exists($field, $normalizedAttribute) && empty($normalizedAttribute[$field])) {
                $normalizedAttribute[$field] = (object)$normalizedAttribute[$field];
            }
        }

        // Add read-only attribute group labels inside attribute
        $normalizedAttribute['group_labels'] = ($attribute->getGroup()) ? $this->translationNormalizer->normalize($attribute->getGroup(), $format, $context) : null;

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
