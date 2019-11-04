<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\Versioning;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Flat family normalizer
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    const ITEM_SEPARATOR = ',';

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /** @var NormalizerInterface */
    protected $translationNormalizer;

    /** @var NormalizerInterface */
    protected $standardNormalizer;

    /**
     * @param NormalizerInterface $standardNormalizer
     * @param NormalizerInterface $translationNormalizer
     */
    public function __construct(
        NormalizerInterface $standardNormalizer,
        NormalizerInterface $translationNormalizer
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param FamilyInterface $family
     *
     * @return array
     */
    public function normalize($family, $format = null, array $context = [])
    {
        $standardFamily = $this->standardNormalizer->normalize($family, 'standard', $context);
        $flatFamily = $standardFamily;

        $flatFamily['attributes'] = implode(self::ITEM_SEPARATOR, $flatFamily['attributes']);

        unset($flatFamily['attribute_requirements']);
        $flatFamily += $this->normalizeRequirements($standardFamily['attribute_requirements']);

        unset($flatFamily['labels']);
        $flatFamily += $this->translationNormalizer->normalize($standardFamily['labels'], 'flat', $context);

        return $flatFamily;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FamilyInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * Normalizes the attribute requirements into a flat array
     *
     * @param array $requirements
     *
     * @return array
     */
    protected function normalizeRequirements(array $requirements)
    {
        $flat = [];
        foreach ($requirements as $channelCode => $attributeCodes) {
            $flat['requirements-' . $channelCode] = implode(self::ITEM_SEPARATOR, $attributeCodes);
        }

        return $flat;
    }
}
