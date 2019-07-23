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

use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetChannelLabelsInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class PublishedProductCompletenessCollectionNormalizer implements NormalizerInterface
{
    /** @var GetChannelLabelsInterface */
    private $getChannelLabels;

    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var NormalizerInterface */
    private $standardNormalizer;

    public function __construct(
        GetChannelLabelsInterface $getChannelLabels,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        NormalizerInterface $standardNormalizer
    ) {
        $this->getChannelLabels = $getChannelLabels;
        $this->localeRepository = $localeRepository;
        $this->attributeRepository = $attributeRepository;
        $this->standardNormalizer = $standardNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($completenesses, $format = null, array $context = []): array
    {
        $normalizedCompletenesses = [];
        $sortedCompletenesses = [];
        $channelCodes = [];
        $localeCodes = [];

        foreach ($completenesses as $completeness) {
            $channelCode = $completeness->channelCode();
            if (!in_array($channelCode, $channelCodes)) {
                $channelCodes[] = $channelCode;
            }

            $localeCode = $completeness->localeCode();
            if (!in_array($localeCode, $localeCodes)) {
                $localeCodes[] = $localeCode;
            }

            $sortedCompletenesses[$channelCode][$localeCode] = $completeness;
        }

        $channelLabels = $this->getChannelLabels->forChannelCodes($channelCodes);

        foreach ($sortedCompletenesses as $channelCode => $channelCompletenesses) {
            $channelCode = (string)$channelCode;
            $normalizedCompletenesses[] = [
                'channel' => $channelCode,
                'labels' => $this->getChannelLabels($channelLabels, $localeCodes, $channelCode),
                'locales' => $this->normalizeChannelCompletenesses(
                    $channelCompletenesses,
                    $format,
                    $localeCodes,
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
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof PublishedProductCompletenessCollection && 'internal_api' === $format;
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
     * @param CompletenessInterface[] $completenesses
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
        array $context
    ) {
        $normalizedCompletenesses = [];

        foreach ($completenesses as $completeness) {
            $localeCode = $completeness->localeCode();

            $normalizedCompleteness = [];
            $normalizedCompleteness['completeness'] = $this->standardNormalizer->normalize($completeness, $format, $context);
            $normalizedCompleteness['missing'] = [];
            $normalizedCompleteness['label'] = $this->localeRepository->findOneByIdentifier($completeness->localeCode())->getName();

            foreach ($completeness->missingAttributeCodes() as $attributeCode) {
                $normalizedCompleteness['missing'][] = [
                    'code' => $attributeCode,
                    'labels' => $this->normalizeAttributeLabels($attributeCode, $localeCodes),
                ];
            }

            $normalizedCompletenesses[$localeCode] = $normalizedCompleteness;
        }

        return $normalizedCompletenesses;
    }

    /**
     * @param string $attributeCode
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
}
