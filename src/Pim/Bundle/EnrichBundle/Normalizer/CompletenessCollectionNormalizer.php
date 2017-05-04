<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\Completeness;
use Pim\Component\Catalog\Model\CompletenessInterface;
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
     * Normalized completeness collection that is returned looks like:
     *
     * [
     *     [
     *         'locale'   => 'de_DE',
     *         'stats'    => [
     *             'total'    => 3,
     *             'complete' => 0,
     *         ],
     *         'channels' => [
     *             'ecommerce' => [
     *                 'completeness' => [
     *                     'required' => 4,
     *                     'missing' => 2,
     *                     'ratio' => 50,
     *                     'locale' => 'de_DE',
     *                     'channel_code' => 'ecommerce',
     *                     'channel_labels' => [
     *                         'en_US' => 'Ecommerce',
     *                         'de_DE' => 'Ecommerce',
     *                         'fr_FR' => 'Ecommerce',
     *                     ],
     *                 ],
     *                 'missing' => [
     *                     [
     *                         'code' = 'description',
     *                         'labels' = [
     *                             'de_DE' => 'Beschreibung',
     *                             'en_US' => 'Description',
     *                             'fr_FR' => 'Description',
     *                         ],
     *                     ],
     *                     ['...'],
     *                 ],
     *             ],
     *             'mobile'    => ['...'],
     *             'print'     => ['...'],
     *         ],
     *     ],
     *     ['...'],
     *     ['...'],
     * ];
     */
    public function normalize($completenesses, $format = null, array $context = [])
    {
        $normalizedCompletenesses = [];
        $localeCodes = [];
        $sortedCompletenesses = [];

        foreach ($completenesses as $completeness) {
            $locale = $completeness->getLocale();
            if (!in_array($locale->getCode(), $localeCodes)) {
                $localeCodes[] = $locale->getCode();
            }

            $sortedCompletenesses[$locale->getCode()][$completeness->getChannel()->getCode()] = $completeness;
        }

        foreach ($sortedCompletenesses as $localeCode => $channelCompletenesses) {
            $normalizedCompletenesses[] = [
                'locale'   => $localeCode,
                'stats'    => [
                    'total'    => count($channelCompletenesses),
                    'complete' => $this->countComplete($channelCompletenesses),
                ],
                'channels' => $this->normalizeChannelCompletenesses(
                    $channelCompletenesses,
                    $format,
                    $context,
                    $localeCodes
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
     * Returns the normalized channel completeness
     *
     * @param CompletenessInterface[] $completenesses
     * @param string                  $format
     * @param array                   $context
     * @param array                   $localeCodes
     *
     * @return array
     */
    protected function normalizeChannelCompletenesses(
        array $completenesses,
        $format,
        array $context,
        array $localeCodes
    ) {
        $normalizedCompletenesses = [];

        //TODO: workaround in order to handle behat empty completeness
        foreach ($completenesses as $completeness) {
            $channelCode = $completeness->getChannel()->getCode();

            $normalizedCompleteness = [];
            $normalizedCompleteness['completeness'] = $this->normalizer->normalize($completeness, $format, $context);
            $normalizedCompleteness['missing'] = [];

            foreach ($completeness->getMissingAttributes() as $attribute) {
                $normalizedCompleteness['missing'][] = [
                    'code'   => $attribute->getCode(),
                    'labels' => $this->normalizeAttributeLabels($attribute, $localeCodes),
                ];
            }

            $normalizedCompletenesses[$channelCode] = $normalizedCompleteness;
        }

        return $normalizedCompletenesses;
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $localeCodes
     *
     * @return array
     */
    protected function normalizeAttributeLabels(AttributeInterface $attribute, array $localeCodes)
    {
        $labels = [];
        foreach ($localeCodes as $locale) {
            $labels[$locale] = $attribute->getTranslation($locale)->getLabel();
        }

        return $labels;
    }
}
