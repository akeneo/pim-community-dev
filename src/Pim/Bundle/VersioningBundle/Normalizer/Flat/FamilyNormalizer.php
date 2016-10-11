<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Normalizer\Standard\FamilyNormalizer as StandardNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Flat family normalizer
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyNormalizer implements NormalizerInterface
{
    const ITEM_SEPARATOR = ',';

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /** @var TranslationNormalizer */
    protected $translationNormalizer;

    /** @var StandardNormalizer */
    protected $standardNormalizer;

    /**
     * @param NormalizerInterface   $standardNormalizer
     * @param TranslationNormalizer $translationNormalizer
     */
    public function __construct(
        NormalizerInterface $standardNormalizer,
        TranslationNormalizer $translationNormalizer
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$this->standardNormalizer->supportsNormalization($object, 'standard')) {
            return null;
        }

        $standardFamily = $this->standardNormalizer->normalize($object, 'standard', $context);
        $flatFamily = $standardFamily;

        $flatFamily['attributes'] = implode(self::ITEM_SEPARATOR, $flatFamily['attributes']);

        unset($flatFamily['attribute_requirements']);
        $flatFamily += $this->normalizeRequirements($standardFamily['attribute_requirements']);

        unset($flatFamily['labels']);
        if ($this->translationNormalizer->supportsNormalization($standardFamily['labels'], 'flat')) {
            $flatFamily += $this->translationNormalizer->normalize($standardFamily['labels'], 'flat', $context);
        }

        return $flatFamily;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FamilyInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalizes the attribute requirements into a flat array
     *
     * @param array $requirements
     *
     * @return array
     */
    protected function normalizeRequirements($requirements)
    {
        $flat = [];
        foreach ($requirements as $channel => $attributes) {
            $flat['requirements-'.$channel] = implode(self::ITEM_SEPARATOR, $attributes);
        }

        return $flat;
    }
}
