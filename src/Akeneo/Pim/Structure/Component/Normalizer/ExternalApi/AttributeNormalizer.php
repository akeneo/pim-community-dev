<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Normalizer\ExternalApi;

use Akeneo\Pim\Structure\Component\AttributeTypes;
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
    public function __construct(
        protected NormalizerInterface $stdNormalizer,
        private NormalizerInterface $translationNormalizer
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        $normalizedAttribute = $this->stdNormalizer->normalize($attribute, 'standard', $context);

        // Add read-only attribute group labels inside attribute
        $normalizedAttribute['group_labels'] = ($attribute->getGroup()) ?
            $this->translationNormalizer->normalize($attribute->getGroup(), $format, $context) :
            null;

        foreach (['labels', 'guidelines', 'group_labels'] as $field) {
            if (\array_key_exists($field, $normalizedAttribute) && [] === $normalizedAttribute[$field]) {
                $normalizedAttribute[$field] = (object)[];
            }
        }

        if (AttributeTypes::IDENTIFIER === $attribute->getType()) {
            $normalizedAttribute['is_main_identifier'] = $attribute->isMainIdentifier();
        }

        return $normalizedAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof AttributeInterface && 'external_api' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
