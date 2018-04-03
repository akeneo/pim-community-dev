<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Component\Catalog\Model\ChannelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform a channel entity into a flat array
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelNormalizer implements NormalizerInterface
{
    const ITEM_SEPARATOR = ',';
    const UNIT_LABEL_PREFIX = 'conversion_unit';

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /** @var NormalizerInterface */
    protected $standardNormalizer;

    /** @var NormalizerInterface */
    protected $translationNormalizer;

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
     * @param ChannelInterface $channel
     *
     * @return array
     */
    public function normalize($channel, $format = null, array $context = [])
    {
        $standardChannel = $this->standardNormalizer->normalize($channel, 'standard', $context);

        $flatChannel = $standardChannel;
        $flatChannel['currencies'] = implode(self::ITEM_SEPARATOR, $standardChannel['currencies']);
        $flatChannel['locales'] = implode(self::ITEM_SEPARATOR, $standardChannel['locales']);

        unset($flatChannel['labels']);
        $flatChannel += $this->translationNormalizer->normalize($standardChannel['labels'], 'flat', $context);

        unset($flatChannel['conversion_units']);
        $flatChannel += $this->normalizeConversionUnits($standardChannel['conversion_units']);

        $flatChannel['category'] = $standardChannel['category_tree'];
        unset($flatChannel['category_tree']);

        return $flatChannel;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ChannelInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalizes the conversion units into a flat array
     *
     * @param array $conversionUnits
     *
     * @return array
     */
    protected function normalizeConversionUnits(array $conversionUnits)
    {
        $flatArray = [];

        foreach ($conversionUnits as $unitType => $unit) {
            $flatArray[self::UNIT_LABEL_PREFIX . '-' . $unitType] = $unit;
        }

        return $flatArray;
    }
}
