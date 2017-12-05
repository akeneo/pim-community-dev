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

namespace PimEnterprise\Component\Api\Normalizer;

use Pim\Component\Api\Hal\Link;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetVariationNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    private $standardNormalizer;

    /** @var RouterInterface */
    private $router;

    /**
     * @param NormalizerInterface $standardNormalizer
     * @param RouterInterface     $router
     */
    public function __construct(NormalizerInterface $standardNormalizer, RouterInterface $router)
    {
        $this->standardNormalizer = $standardNormalizer;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $standardNormalizedVariation = $this->standardNormalizer->normalize($object, $format, $context);
        $localeCode = $standardNormalizedVariation['locale'] ?: 'no_locale';

        $route = $this->router->generate(
            'pim_api_asset_variation_download',
            [
                'code' => $standardNormalizedVariation['asset'],
                'channelCode' => $standardNormalizedVariation['channel'],
                'localeCode' => $localeCode,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $link = new Link('download', $route);

        $normalizedVariation = [
            '_link' => $link->toArray(),
            'locale' => 'no_locale' !== $localeCode ? $localeCode : null,
            'channel' => $standardNormalizedVariation['channel'],
            'code' => $standardNormalizedVariation['code'],
        ];

        return $normalizedVariation;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof VariationInterface && 'external_api' === $format;
    }
}
