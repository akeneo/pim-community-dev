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

use Pim\Component\Api\Hal\Link;
use PimEnterprise\Bundle\ApiBundle\Controller\AssetVariationController;
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
    private $componentNormalizer;

    /** @var RouterInterface */
    private $router;

    /**
     * @param NormalizerInterface $componentNormalizer
     * @param RouterInterface     $router
     */
    public function __construct(NormalizerInterface $componentNormalizer, RouterInterface $router)
    {
        $this->componentNormalizer = $componentNormalizer;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($variation, $format = null, array $context = []): array
    {
        $normalizedVariation = $this->componentNormalizer->normalize($variation, $format, $context);

        $route = $this->router->generate(
            'pim_api_asset_variation_download',
            [
                'code' => $variation->getAsset()->getCode(),
                'channelCode' => $normalizedVariation['channel'],
                'localeCode' => $normalizedVariation['locale'] ?: AssetVariationController::NON_LOCALIZABLE_VARIATION,
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
}
