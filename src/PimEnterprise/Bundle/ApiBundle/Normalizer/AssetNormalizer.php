<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ApiBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Api\Hal\Link;
use PimEnterprise\Bundle\ApiBundle\Controller\AssetReferenceController;
use PimEnterprise\Bundle\ApiBundle\Controller\AssetVariationController;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    private $standardAssetNormalizer;

    /** @var NormalizerInterface */
    private $variationNormalizer;

    /** @var NormalizerInterface */
    private $referenceNormalizer;

    /** @var RouterInterface */
    private $router;

    /**
     * @param NormalizerInterface $standardAssetNormalizer
     * @param NormalizerInterface $variationNormalizer
     * @param NormalizerInterface $referenceNormalizer
     * @param RouterInterface     $router
     */
    public function __construct(
        NormalizerInterface $standardAssetNormalizer,
        NormalizerInterface $variationNormalizer,
        NormalizerInterface $referenceNormalizer,
        RouterInterface $router
    ) {
        $this->standardAssetNormalizer = $standardAssetNormalizer;
        $this->variationNormalizer = $variationNormalizer;
        $this->referenceNormalizer = $referenceNormalizer;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($asset, $format = null, array $context = []): array
    {
        $normalizedAsset = $this->standardAssetNormalizer->normalize($asset, 'standard', $context);
        $normalizedAsset['variation_files'] = $this->normalizeVariations($asset->getVariations(), $context);
        $normalizedAsset['reference_files'] = $this->normalizeReferences($asset->getReferences(), $context);

        return $normalizedAsset;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof AssetInterface && 'external_api' === $format;
    }

    /**
     * Normalizes the asset variations to the web API format:
     *
     * [
     *     [
     *         '_link' => [
     *             'download' => [
     *                 'href' => '{uriRoot}/assets/asset_viewable_by_everybody/variation-files/print/no-locale/download'
     *             ],
     *             'self' => [
     *                 'href' => '{uriRoot}/assets/asset_viewable_by_everybody/variation-files/print/no-locale'
     *             ]
     *         ],
     *         'locale' =>null,
     *         'channel' => 'print',
     *         'code' => 'f/4/d/1/f4d12...cc23535_imageA_variationB.jpg'
     *     ]
     * ]
     *
     * There will be only one variation per channel if the asset is not localized
     * and one per channel and locale if it is localized.
     *
     * @param VariationInterface[] $variations
     * @param array                $context
     *
     * @return array
     */
    private function normalizeVariations(array $variations, array $context = []): array
    {
        $normalizedVariations = [];

        foreach ($variations as $variation) {
            $normalizedVariation = $this->variationNormalizer->normalize($variation, 'external_api', $context);

            if (null !== $normalizedVariation['code']) {
                $route = $this->router->generate(
                    'pimee_api_asset_variation_get',
                    [
                        'code' => $variation->getAsset()->getCode(),
                        'channelCode' => $normalizedVariation['channel'],
                        'localeCode' => $normalizedVariation['locale']
                            ?: AssetVariationController::NON_LOCALIZABLE_VARIATION,
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $link = new Link('self', $route);

                $normalizedVariation['_link'] = array_merge($normalizedVariation['_link'], $link->toArray());

                $normalizedVariations[] = $normalizedVariation;
            }
        }

        return $normalizedVariations;
    }

    /**
     * Normalizes the asset references to the web API format:
     * [
     *     [
     *         '_link' => [
     *             'download' => [
     *                 'href' => '{uriRoot}/assets/asset_viewable_by_everybody/reference-files/no-locale/download'
     *             ],
     *             'self' => [
     *                 'href' => '{uriRoot}/assets/asset_viewable_by_everybody/reference-files/no-locale'
     *             ]
     *         ],
     *         'locale' => null,
     *         'code' => 'f/4/d/1/f4d12...cc23535_imageA.jpg'
     *     ]
     * ]
     *
     * There will be only one reference if the asset is not localized, and as
     * many as there is active locales if it is localized.
     *
     * @param Collection $references
     * @param array      $context
     *
     * @return array
     */
    private function normalizeReferences(Collection $references, array $context): array
    {
        $normalizedReferences = [];

        foreach ($references as $reference) {
            $normalizedReference = $this->referenceNormalizer->normalize($reference, 'external_api', $context);

            if (null !== $normalizedReference['code']) {
                $route = $this->router->generate(
                    'pimee_api_asset_reference_get',
                    [
                        'code'       => $reference->getAsset()->getCode(),
                        'localeCode' => $normalizedReference['locale']
                            ?: AssetReferenceController::NON_LOCALIZABLE_REFERENCE,
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $link = new Link('self', $route);

                $normalizedReference['_link'] = array_merge($normalizedReference['_link'], $link->toArray());

                $normalizedReferences[] = $normalizedReference;
            }
        }

        return $normalizedReferences;
    }
}
