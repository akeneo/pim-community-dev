<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetChannelLabelsInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
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
class ProductCompletenessCollectionNormalizer
{
    /** @var NormalizerInterface */
    private $normalizer;

    /** @var GetChannelLabelsInterface */
    private $getChannelLabels;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        NormalizerInterface $normalizer,
        GetChannelLabelsInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->normalizer = $normalizer;
        $this->getChannelLabels = $channelRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param ProductCompletenessCollection $completenesses
     *
     * @return array
     */
    public function normalize(ProductCompletenessCollection $completenesses): array
    {
        $channelCodes = $this->getChannelCodes($completenesses);
        $localeCodes = $this->getLocaleCodes($completenesses);
        $completenessesByChannel = $this->getCompletenessesByChannel($completenesses);

        $channelLabels = $this->getChannelLabels->getLabels($channelCodes);

        $normalizedCompletenessesPerChannel = [];
        foreach ($completenessesByChannel as $channelCode => $channelCompletenesses) {
            $normalizedCompletenessesPerChannel[] = $this->normalizeChannelCompletenesses(
                $channelCode,
                $channelCompletenesses,
                $channelLabels,
                $localeCodes
            );
        }

        return $normalizedCompletenessesPerChannel;
    }

    /**
     * @param ProductCompletenessCollection $completenesses
     *
     * @return string[]
     */
    private function getChannelCodes(ProductCompletenessCollection $completenesses): array
    {
        $channelCodes = [];
        foreach ($completenesses as $completeness) {
            $channelCodes[] = $completeness->channelCode();
        }

        return array_values(array_unique($channelCodes));
    }

    /**
     * @param ProductCompletenessCollection $completenesses
     *
     * @return string[]
     */
    private function getLocaleCodes(ProductCompletenessCollection $completenesses): array
    {
        $localeCodes = [];
        foreach ($completenesses as $completeness) {
            $localeCodes[] = $completeness->localeCode();
        }

        return array_values(array_unique($localeCodes));
    }

    /**
     * @param ProductCompletenessCollection $completenesses
     *
     * @return array
     */
    private function getCompletenessesByChannel(ProductCompletenessCollection $completenesses): array
    {
        $sortedCompletenesses = [];
        foreach ($completenesses as $completeness) {
            $sortedCompletenesses[$completeness->channelCode()][] = $completeness;
        }

        return $sortedCompletenesses;
    }

    /**
     * @param string                $channelCode
     * @param ProductCompleteness[] $channelCompletenesses
     * @param array                 $channelLabels
     * @param string[]              $localeCodes
     *
     * @return array
     */
    private function normalizeChannelCompletenesses(
        string $channelCode,
        array $channelCompletenesses,
        array $channelLabels,
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
            'locales' => $this->normalizeCompletenessesByLocale($channelCompletenesses, $localeCodes),
        ];
    }

    /**
     * Returns how many completenesses have a ratio of 100 for a provided list of completeness.
     *
     * @param ProductCompleteness[] $completenesses
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
     * @param ProductCompleteness[] $completenesses
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
     * @param ProductCompleteness[] $completenesses
     * @param string[]              $localeCodes
     *
     * @return array
     */
    private function normalizeCompletenessesByLocale(
        array $completenesses,
        array $localeCodes
    ): array {
        $normalizedCompletenesses = [];
        foreach ($completenesses as $completeness) {
            $normalizedCompletenesses[$completeness->localeCode()] = [
                'completeness' => $this->normalizer->normalize($completeness, 'internal_api'),
                'missing' => array_map(function ($attributeCode) use ($localeCodes) {
                    return [
                        'code'   => $attributeCode,
                        'labels' => $this->normalizeAttributeLabels($attributeCode, $localeCodes),
                    ];
                }, $completeness->missingAttributeCodes()),
                'label' => $this->getLocaleName($completeness->localeCode()),
            ];
        }

        return $normalizedCompletenesses;
    }

    /**
     * @param string   $attributeCode
     * @param string[] $localeCodes
     *
     * @return array
     */
    private function normalizeAttributeLabels(string $attributeCode, array $localeCodes): array
    {
        $result = [];
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

        foreach ($localeCodes as $localeCode) {
            $result[$localeCode] = $attribute->getTranslation($localeCode)->getLabel();
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
     * @return string|null
     */
    private function getLocaleName(string $localeCode): ?string
    {
        $locale = new Locale();
        $locale->setCode($localeCode);

        return $locale->getName();
    }
}
