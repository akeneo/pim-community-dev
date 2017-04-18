<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Completeness normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessCollectionNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($completenesses, $format = null, array $context = [])
    {
        $normalizedCompleteness = [];
        $locales = [];
        foreach ($completenesses as $completeness) {
            $locales[] = $completeness['locale'];
        }

        foreach ($completenesses as $completeness) {
            $locale = $completeness['locale'];
            $channels = $completeness['channels'];
            $stats = $completeness['stats'];

            $normalizedCompChannels = $this->normalizeChannelCompleteness(
                $format,
                $context,
                $channels,
                $locales
            );

            $normalizedCompleteness[] = [
                'locale'   => $locale,
                'stats'    => $stats,
                'channels' => $normalizedCompChannels,
            ];
        }

        return $normalizedCompleteness;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return false;
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $locales
     *
     * @return array
     */
    protected function normalizeAttributeLabels(AttributeInterface $attribute, array $locales)
    {
        $labels = [];
        foreach ($locales as $locale) {
            $labels[$locale] = $attribute->getTranslation($locale)->getLabel();
        }

        return $labels;
    }

    /**
     * Returns the normalized channel completeness
     *
     * @param string $format
     * @param array  $context
     * @param array  $channels
     * @param array  $locales
     *
     * @return array
     */
    protected function normalizeChannelCompleteness($format, array $context, array $channels, array $locales)
    {
        $normalizedCompChannels = [];

        //TODO: workaround in order to handle behat empty completeness
        foreach ($channels as $channelCompleteness) {
            $channelCode = null;
            if (null !== $channelCompleteness['completeness']) {
                $channelCode = $channelCompleteness['completeness']->getChannel()->getCode();
            }

            if (null !== $channelCode) {
                $attributes = $channelCompleteness['missing'];
                $normChannel = [];
                $normChannel['completeness'] = $this->normalizer->normalize(
                    $channelCompleteness['completeness'],
                    $format,
                    $context
                );

                $normChannel['missing'] = [];

                foreach ($attributes as $attribute) {
                    $normChannel['missing'][] = [
                        'code'   => $attribute->getCode(),
                        'labels' => $this->normalizeAttributeLabels($attribute, $locales)
                    ];
                }

                $normalizedCompChannels[$channelCode] = $normChannel;
            }
        }

        return $normalizedCompChannels;
    }
}
