<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetAttributeLabelsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetChannelLabelsInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class PublishedProductCompletenessCollectionNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    private $standardNormalizer;

    /** @var GetChannelLabelsInterface */
    private $getChannelLabels;

    /** @var GetAttributeLabelsInterface */
    private $getAttributeLabels;


    public function __construct(
        NormalizerInterface $standardNormalizer,
        GetChannelLabelsInterface $getChannelLabels,
        GetAttributeLabelsInterface $getAttributeLabels
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->getChannelLabels = $getChannelLabels;
        $this->getAttributeLabels = $getAttributeLabels;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($completenesses, $format = null, array $context = []): array
    {
        $channelCodes = $this->getChannelCodes($completenesses);
        $localeCodes = $this->getLocaleCodes($completenesses);
        $missingAttributeCodes = $this->getMissingAttributeCodes($completenesses);
        $completenessesByChannel = $this->getCompletenessesByChannel($completenesses);

        $channelLabels = $this->getChannelLabels->forChannelCodes($channelCodes);
        $attributeLabels = $this->getAttributeLabels->forAttributeCodes($missingAttributeCodes);

        $normalizedCompletenesses = [];
        foreach ($completenessesByChannel as $channelCode => $channelCompletenesses) {
            $normalizedCompletenesses[] = [
                'channel' => $channelCode,
                'labels' => $this->getChannelLabels($channelLabels, $localeCodes, $channelCode),
                'locales' => $this->normalizeChannelCompletenesses(
                    $channelCompletenesses,
                    $format,
                    $localeCodes,
                    $attributeLabels,
                    $context
                ),
                'stats' => [
                    'total' => count($channelCompletenesses),
                    'complete' => $this->countComplete($channelCompletenesses),
                    'average' => $this->average($channelCompletenesses),
                ],
            ];
        }

        return $normalizedCompletenesses;
    }

    /**
     * @param PublishedProductCompletenessCollection $completenesses
     *
     * @return string[]
     */
    private function getChannelCodes(PublishedProductCompletenessCollection $completenesses): array
    {
        $channelCodes = [];
        foreach ($completenesses as $completeness) {
            $channelCodes[] = $completeness->channelCode();
        }

        return array_values(array_unique($channelCodes));
    }

    /**
     * @param PublishedProductCompletenessCollection $completenesses
     *
     * @return string[]
     */
    private function getMissingAttributeCodes(PublishedProductCompletenessCollection $completenesses): array
    {
        $attributeCodes = [];
        foreach ($completenesses as $completeness) {
            $attributeCodes = array_merge($attributeCodes, $completeness->missingAttributeCodes());
        }

        return array_values(array_unique($attributeCodes));
    }

    /**
     * @param PublishedProductCompletenessCollection $completenesses
     *
     * @return string[]
     */
    private function getLocaleCodes(PublishedProductCompletenessCollection $completenesses): array
    {
        $localeCodes = [];
        foreach ($completenesses as $completeness) {
            $localeCodes[] = $completeness->localeCode();
        }

        return array_values(array_unique($localeCodes));
    }

    /**
     * @param PublishedProductCompletenessCollection $completenesses
     *
     * @return array
     */
    private function getCompletenessesByChannel(PublishedProductCompletenessCollection $completenesses): array
    {
        $sortedCompletenesses = [];
        foreach ($completenesses as $completeness) {
            $sortedCompletenesses[$completeness->channelCode()][] = $completeness;
        }

        return $sortedCompletenesses;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof PublishedProductCompletenessCollection && 'internal_api' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * Returns how many completenesses have a ratio of 100 for a provided list of completeness.
     *
     * @param PublishedProductCompleteness[] $completenesses
     *
     * @return int
     */
    private function countComplete(array $completenesses)
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
     * @param PublishedProductCompleteness[] $completenesses
     *
     * @return int
     */
    private function average(array $completenesses)
    {
        $complete = 0;
        foreach ($completenesses as $completeness) {
            $complete += $completeness->ratio();
        }

        return (int)round($complete / count($completenesses));
    }

    /**
     * Returns the normalized channel completeness
     *
     * @param PublishedProductCompleteness[] $completenesses
     * @param string $format
     * @param string[] $localeCodes
     * @param array $context
     *
     * @return array
     */
    private function normalizeChannelCompletenesses(
        array $completenesses,
        $format,
        array $localeCodes,
        array $attributeLabels,
        array $context
    ) {
        $normalizedCompletenesses = [];

        foreach ($completenesses as $completeness) {
            $localeCode = $completeness->localeCode();

            $normalizedCompleteness = [];
            $normalizedCompleteness['completeness'] = $this->standardNormalizer->normalize($completeness, $format, $context);
            $normalizedCompleteness['missing'] = [];
            $normalizedCompleteness['label'] = $this->getLocaleName($completeness->localeCode());

            foreach ($completeness->missingAttributeCodes() as $attributeCode) {
                $normalizedCompleteness['missing'][] = [
                    'code' => $attributeCode,
                    'labels' => $this->normalizeAttributeLabels($attributeLabels, $attributeCode, $localeCodes),
                ];
            }

            $normalizedCompletenesses[$localeCode] = $normalizedCompleteness;
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
