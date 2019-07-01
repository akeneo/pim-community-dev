<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * TODO This class can be far better optimized!
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCompletenessCollectionNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    private $normalizer;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->normalizer = $normalizer;
        $this->channelRepository = $channelRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @param ProductCompleteness[] $completenesses
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

        $channels = $this->channelRepository->findBy(['code' => $channelCodes]);

        foreach ($sortedCompletenesses as $channelCode => $channelCompletenesses) {
            $channelCode = (string) $channelCode;
            $normalizedCompletenesses[] = [
                'channel'   => $channelCode,
                'labels'    => $this->getChannelLabels($channels, $localeCodes, $channelCode),
                'stats'    => [
                    'total'    => count($channelCompletenesses),
                    'complete' => $this->countComplete($channelCompletenesses),
                    'average'  => $this->average($channelCompletenesses),
                ],
                'locales' => $this->normalizeChannelCompletenesses(
                    $channelCompletenesses,
                    $format,
                    $localeCodes,
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
    private function countComplete(array $completenesses)
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
    private function average(array $completenesses)
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
     * @param ProductCompleteness[] $completenesses
     * @param string                $format
     * @param string[]              $localeCodes
     * @param array                 $context
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

        //TODO: workaround in order to handle behat empty completeness
        foreach ($completenesses as $completeness) {
            $localeCode = $completeness->localeCode();

            $normalizedCompleteness = [];
            $normalizedCompleteness['completeness'] = $this->normalizer->normalize($completeness, $format, $context);
            $normalizedCompleteness['missing'] = [];
            $normalizedCompleteness['label'] = $completeness->getLocale()->getName();

            foreach ($completeness->missingAttributeCodes() as $attributeCode) {
                $normalizedCompleteness['missing'][] = [
                    'code'   => $attributeCode,
                    'labels' => $this->normalizeAttributeLabels($attributeCode, $localeCodes),
                ];
            }

            $normalizedCompletenesses[$localeCode] = $normalizedCompleteness;
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
     * @param ChannelInterface[] $channels
     * @param string[]           $localeCodes
     * @param string             $channelCode
     *
     * @return string[]
     */
    private function getChannelLabels(array $channels, array $localeCodes, string $channelCode): array
    {
        $matchingChannels = array_filter($channels, function (ChannelInterface $channel) use ($channelCode) {
            return $channel->getCode() === $channelCode;
        });
        $channel = array_shift($matchingChannels);

        return array_reduce($localeCodes, function ($result, $localeCode) use ($channel) {
            $result[$localeCode] = $channel->getTranslation($localeCode)->getLabel();

            return $result;
        }, []);
    }
}
