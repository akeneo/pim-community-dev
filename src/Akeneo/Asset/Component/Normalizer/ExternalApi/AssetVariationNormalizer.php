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

namespace Akeneo\Asset\Component\Normalizer\ExternalApi;

use Akeneo\Asset\Component\Model\VariationInterface;
use Akeneo\Tool\Component\Api\Hal\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetVariationNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
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
    public function normalize($variation, $format = null, array $context = []): array
    {
        $standardNormalizedVariation = $this->standardNormalizer->normalize($variation, $format, $context);

        $normalizedVariation = [
            'locale' => $standardNormalizedVariation['locale'],
            'scope' => $standardNormalizedVariation['channel'],
            'code' => $standardNormalizedVariation['code'],
        ];

        $route = $this->router->generate(
            'pimee_api_asset_variation_download',
            [
                'code' => $variation->getAsset()->getCode(),
                'channelCode' => $normalizedVariation['scope'],
                'localeCode' => $normalizedVariation['locale'] ?: 'no-locale',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $link = new Link('download', $route);

        return array_merge(
            ['_link' => $link->toArray()],
            $normalizedVariation
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof VariationInterface && 'external_api' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
