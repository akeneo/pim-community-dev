<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\scalar;

/**
 * {description}
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MissingRequiredAttributesNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($requiredMissingAttributes, $format = null, array $context = []): array
    {
        $locales = $context['locales'] ?? [];

        $normalizedMissingRequiredAttributes = [];
        foreach ($requiredMissingAttributes as $attribute) {
            $attribute = $attribute;
            $normalizedMissingRequiredAttributes[] = [
                'code'   => $attribute->getCode(),
                'labels' => $this->normalizeAttributeLabels($attribute, $locales),
            ];
        }

        return $normalizedMissingRequiredAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return false;
    }

    /**
     * Normalizes the label of the missing required attributes.
     *
     * @param AttributeInterface $attribute
     * @param LocaleInterface[]  $localeCodes
     *
     * @return array
     */
    private function normalizeAttributeLabels(AttributeInterface $attribute, array $localeCodes): array
    {
        $result = [];
        foreach ($localeCodes as $localeCode) {
            $result[$localeCode] = $attribute->getTranslation($localeCode)->getLabel();
        }

        return $result;
    }
}
