<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
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
     *
     * @param CompletenessInterface[] $completenesses
     *
     * Normalized completeness collection that is returned looks like:
     *
     * [
     *     [
     *         'channel'  => 'ecommerce',
     *         'labels'   => [
     *             'en_US' => 'Ecommerce',
     *             'fr_FR' => 'E-commerce',
     *         ],
     *         'stats'    => [
     *             'total'    => 3,
     *             'complete' => 0,
     *         ],
     *         'locales' => [
     *             'de_DE' => [
     *                 'completeness' => [
     *                     'required' => 4,
     *                     'missing' => 2,
     *                     'ratio' => 50,
     *                     'locale' => 'de_DE',
     *                     'channel' => 'ecommerce'
     *                 ],
     *                 'missing' => [
     *                     [
     *                         'code' = 'description',
     *                         'labels' = [
     *                             'en_US' => 'Description',
     *                             'fr_FR' => 'Description'
     *                         ]
     *                     ],
     *                     ['...'],
     *                 ],
     *             ],
     *             'fr_FR'    => ['...'],
     *             'en_US'     => ['...'],
     *         ],
     *     ],
     *     ['...'],
     *     ['...'],
     * ];
     */
    public function normalize($completenesses, $format = null, array $context = [])
    {
        $normalizedCompletenesses = [];
        $sortedCompletenesses = [];
        $channels = [];
        $locales = [];

        foreach ($completenesses as $completeness) {
            $channel = $completeness->getChannel();
            if (!in_array($channel, $channels)) {
                $channels[] = $channel;
            }

            $locale = $completeness->getLocale();
            if (!in_array($locale, $locales)) {
                $locales[] = $locale;
            }

            $sortedCompletenesses[$channel->getCode()][$completeness->getLocale()->getCode()] = $completeness;
        }

        foreach ($sortedCompletenesses as $channelCode => $channelCompletenesses) {
            $channelCode = (string) $channelCode;
            $normalizedCompletenesses[] = [
                'channel'   => $channelCode,
                'labels'    => $this->getChannelLabels($channels, $locales, $channelCode),
                'stats'    => [
                    'total'    => count($channelCompletenesses),
                    'complete' => $this->countComplete($channelCompletenesses),
                    'average'  => $this->average($channelCompletenesses),
                ],
                'locales' => $this->normalizeChannelCompletenesses(
                    $channelCompletenesses,
                    $format,
                    $locales,
                    $context
                ),
            ];
        }

        return $normalizedCompletenesses;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return false;
    }

    /**
     * Returns how many completenesses have a ratio of 100 for a provided list of completeness.
     *
     * @param CompletenessInterface[] $completenesses
     *
     * @return int
     */
    protected function countComplete(array $completenesses)
    {
        $complete = 0;
        foreach ($completenesses as $completeness) {
            if (100 <= $completeness->getRatio()) {
                $complete++;
            }
        }

        return $complete;
    }

    /**
     * Returns the average completeness of a specific channel
     *
     * @param CompletenessInterface[] $completenesses
     *
     * @return int
     */
    protected function average(array $completenesses)
    {
        $complete = 0;
        foreach ($completenesses as $completeness) {
            $complete += $completeness->getRatio();
        }

        return (int) round($complete / count($completenesses));
    }

    /**
     * Returns the normalized channel completeness
     *
     * @param CompletenessInterface[] $completenesses
     * @param string                  $format
     * @param LocaleInterface[]       $locales
     * @param array                   $context
     *
     * @return array
     */
    protected function normalizeChannelCompletenesses(
        array $completenesses,
        $format,
        array $locales,
        array $context
    ) {
        $normalizedCompletenesses = [];

        //TODO: workaround in order to handle behat empty completeness
        foreach ($completenesses as $completeness) {
            $localeCode = $completeness->getLocale()->getCode();

            $normalizedCompleteness = [];
            $normalizedCompleteness['completeness'] = $this->normalizer->normalize($completeness, $format, $context);
            $normalizedCompleteness['missing'] = [];
            $normalizedCompleteness['label'] = $completeness->getLocale()->getName();

            foreach ($completeness->getMissingAttributes() as $attribute) {
                $normalizedCompleteness['missing'][] = [
                    'code'   => $attribute->getCode(),
                    'labels' => $this->normalizeAttributeLabels($attribute, $locales),
                ];
            }

            $normalizedCompletenesses[$localeCode] = $normalizedCompleteness;
        }

        return $normalizedCompletenesses;
    }

    /**
     * @param AttributeInterface $attribute
     * @param LocaleInterface[]  $locales
     *
     * @return array
     */
    protected function normalizeAttributeLabels(AttributeInterface $attribute, $locales): array
    {
        $result = [];
        foreach ($locales as $locale) {
            $result[$locale->getCode()] = $attribute->getTranslation($locale->getCode())->getLabel();
        }

        return $result;
    }

    /**
     * @param ChannelInterface[] $channels
     * @param LocaleInterface[]  $locales
     * @param string             $channelCode
     *
     * @return string[]
     */
    protected function getChannelLabels(array $channels, array $locales, string $channelCode)
    {
        $matchingChannels = array_filter($channels, function (ChannelInterface $channel) use ($channelCode) {
            return $channel->getCode() === $channelCode;
        });
        $channel = array_shift($matchingChannels);

        return array_reduce($locales, function ($result, LocaleInterface $locale) use ($channel) {
            $result[$locale->getCode()] = $channel->getTranslation($locale->getCode())->getLabel();

            return $result;
        }, []);
    }
}
