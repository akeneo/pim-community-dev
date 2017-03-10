<?php

namespace Pim\Component\Api\Normalizer;

use Pim\Component\Catalog\Model\FamilyInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyNormalizer implements NormalizerInterface
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
    public function normalize($family, $format = null, array $context = [])
    {
        $normalizedFamily = $this->stdNormalizer->normalize($family, 'standard', $context);

        if (empty($normalizedFamily['labels'])) {
            $normalizedFamily['labels'] = (object) $normalizedFamily['labels'];
        }

        if (empty($normalizedFamily['attribute_requirements'])) {
            $normalizedFamily['attribute_requirements'] = (object) $normalizedFamily['attribute_requirements'];
        }

        return $normalizedFamily;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FamilyInterface && 'external_api' === $format;
    }
}
