<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetAttributeLabelsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetChannelLabelsInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes a ProductCompletenessCollection of a product (for the Product Edit Form)
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
 *                 'label': 'German'
 *             ],
 *             'fr_FR'    => ['...'],
 *             'en_US'     => ['...'],
 *         ],
 *     ],
 *     ['...'],
 *     ['...'],
 * ]
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCompletenessWithMissingAttributeCodesCollectionNormalizer
{
    /** @var NormalizerInterface */
    private $normalizer;

    /** @var GetChannelLabelsInterface */
    private $getChannelLabels;

    /** @var GetAttributeLabelsInterface */
    private $getAttributeLabels;

    public function __construct(
        NormalizerInterface $normalizer,
        GetChannelLabelsInterface $getChannelLabels,
        GetAttributeLabelsInterface $getAttributeLabels
    ) {
        $this->normalizer = $normalizer;
        $this->getChannelLabels = $getChannelLabels;
        $this->getAttributeLabels = $getAttributeLabels;
    }

    /**
     * @param ProductCompletenessWithMissingAttributeCodesCollection $completenesses
     *
     * @return array
     */
    public function normalize(ProductCompletenessWithMissingAttributeCodesCollection $completenesses): array
    {
        $channelCodes = $this->getChannelCodes($completenesses);
        $localeCodes = $this->getLocaleCodes($completenesses);
        $missingAttributeCodes = $this->getMissingAttributeCodes($completenesses);
        $completenessesByChannel = $this->getCompletenessesByChannel($completenesses);

        $channelLabels = $this->getChannelLabels->forChannelCodes($channelCodes);
        $attributeLabels = $this->getAttributeLabels->forAttributeCodes($missingAttributeCodes);

        $normalizedCompletenessesPerChannel = [];
        foreach ($completenessesByChannel as $channelCode => $channelCompletenesses) {
            $normalizedCompletenessesPerChannel[] = $this->normalizeChannelCompletenesses(
                $channelCode,
                $channelCompletenesses,
                $channelLabels,
                $attributeLabels,
                $localeCodes
            );
        }

        return $normalizedCompletenessesPerChannel;
    }

    /**
     * @param ProductCompletenessWithMissingAttributeCodesCollection $completenesses
     *
     * @return string[]
     */
    private function getChannelCodes(ProductCompletenessWithMissingAttributeCodesCollection $completenesses): array
    {
        $channelCodes = [];
        foreach ($completenesses as $completeness) {
            $channelCodes[] = $completeness->channelCode();
        }

        return array_values(array_unique($channelCodes));
    }

    /**
     * @param ProductCompletenessWithMissingAttributeCodesCollection $completenesses
     *
     * @return string[]
     */
    private function getMissingAttributeCodes(ProductCompletenessWithMissingAttributeCodesCollection $completenesses): array
    {
        $attributeCodes = [];
        foreach ($completenesses as $completeness) {
            $attributeCodes = array_merge($attributeCodes, $completeness->missingAttributeCodes());
        }

        return array_values(array_unique($attributeCodes));
    }

    /**
     * @param ProductCompletenessWithMissingAttributeCodesCollection $completenesses
     *
     * @return string[]
     */
    private function getLocaleCodes(ProductCompletenessWithMissingAttributeCodesCollection $completenesses): array
    {
        $localeCodes = [];
        foreach ($completenesses as $completeness) {
            $localeCodes[] = $completeness->localeCode();
        }

        return array_values(array_unique($localeCodes));
    }

    /**
     * @param ProductCompletenessWithMissingAttributeCodesCollection $completenesses
     *
     * @return array
     */
    private function getCompletenessesByChannel(ProductCompletenessWithMissingAttributeCodesCollection $completenesses): array
    {
        $sortedCompletenesses = [];
        foreach ($completenesses as $completeness) {
            $sortedCompletenesses[$completeness->channelCode()][] = $completeness;
        }

        return $sortedCompletenesses;
    }

    /**
     * @param string                $channelCode
     * @param ProductCompletenessWithMissingAttributeCodes[] $channelCompletenesses
     * @param array                 $channelLabels
     * @param array                 $attributeLabels
     * @param string[]              $localeCodes
     *
     * @return array
     */
    private function normalizeChannelCompletenesses(
        string $channelCode,
        array $channelCompletenesses,
        array $channelLabels,
        array $attributeLabels,
        array $localeCodes
    ): array {
        return [
            'channel'   => $channelCode,
            'labels'    => $this->getChannelLabels($channelLabels, $localeCodes, $channelCode),
            'stats'    => [
                'total'    => count($channelCompletenesses),
                'complete' => $this->countComplete($channelCompletenesses),
                'average'  => $this->average($channelCompletenesses),
            ],
            'locales' => $this->normalizeCompletenessesByLocale($channelCompletenesses, $localeCodes, $attributeLabels),
        ];
    }

    /**
     * Returns how many completenesses have a ratio of 100 for a provided list of completeness.
     *
     * @param ProductCompletenessWithMissingAttributeCodes[] $completenesses
     *
     * @return int
     */
    private function countComplete(array $completenesses): int
    {
        $complete = 0;
        foreach ($completenesses as $completeness) {
            if (100 <= $completeness->ratio()) {
                $complete++;
            }
        }

        return $complete;
    }

    /**
     * Returns the average completeness of a specific channel
     *
     * @param ProductCompletenessWithMissingAttributeCodes[] $completenesses
     *
     * @return int
     */
    private function average(array $completenesses): int
    {
        $complete = 0;
        foreach ($completenesses as $completeness) {
            $complete += $completeness->ratio();
        }

        return (int) round($complete / count($completenesses));
    }

    /**
     * Returns the normalized channel completeness
     *
     * @param ProductCompletenessWithMissingAttributeCodes[] $completenesses
     * @param string[]              $localeCodes
     * @param array                 $attributeLabels
     *
     * @return array
     */
    private function normalizeCompletenessesByLocale(
        array $completenesses,
        array $localeCodes,
        array $attributeLabels
    ): array {
        $normalizedCompletenesses = [];
        foreach ($completenesses as $completeness) {
            $normalizedCompletenesses[$completeness->localeCode()] = [
                'completeness' => $this->normalizer->normalize($completeness, 'internal_api'),
                'missing' => array_map(function ($attributeCode) use ($localeCodes, $attributeLabels) {
                    return [
                        'code'   => $attributeCode,
                        'labels' => $this->normalizeAttributeLabels($attributeLabels, $attributeCode, $localeCodes),
                    ];
                }, $completeness->missingAttributeCodes()),
                'label' => $this->getLocaleName($completeness->localeCode()),
            ];
        }

        return $normalizedCompletenesses;
    }

    /**
     * @param array    $attributeLabels
     * @param string   $attributeCode
     * @param string[] $localeCodes
     *
     * @return array
     */
    private function normalizeAttributeLabels(array $attributeLabels, string $attributeCode, array $localeCodes): array
    {
        $result = [];
        foreach ($localeCodes as $localeCode) {
            $label = '[' . $attributeCode . ']';
            if (isset($attributeLabels[$attributeCode][$localeCode])) {
                $label = $attributeLabels[$attributeCode][$localeCode];
            }
            $result[$localeCode] = $label;
        }

        return $result;
    }

    /**
     * @param array    $channelLabels
     * @param string[] $localeCodes
     * @param string   $channelCode
     *
     * @return string[]
     */
    private function getChannelLabels(array $channelLabels, array $localeCodes, string $channelCode): array
    {
        $result = [];
        foreach ($localeCodes as $localeCode) {
            $label = '[' . $channelCode . ']';
            if (isset($channelLabels[$channelCode][$localeCode])) {
                $label = $channelLabels[$channelCode][$localeCode];
            }

            $result[$localeCode] = $label;
        }

        return $result;
    }

    /**
     * @param string $localeCode
     *
     * @return string
     */
    private function getLocaleName(string $localeCode): string
    {
        return \Locale::getDisplayName($localeCode);
    }
}
