<?php

declare(strict_types=1);

namespace Pim\Component\Api\Normalizer;

use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantNormalizer implements NormalizerInterface
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
    public function normalize($familyVariant, $format = null, array $context = [])
    {
        $normalizedFamilyVariant = $this->stdNormalizer->normalize($familyVariant, 'standard', $context);

        unset($normalizedFamilyVariant['family']);

        if (empty($normalizedFamilyVariant['labels'])) {
            $normalizedFamilyVariant['labels'] = (object) $normalizedFamilyVariant['labels'];
        }

        return $normalizedFamilyVariant;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FamilyVariantInterface && 'external_api' === $format;
    }
}
