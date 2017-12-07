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
use PimEnterprise\Bundle\ApiBundle\Controller\AssetReferenceController;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetReferenceNormalizer implements NormalizerInterface
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
    public function normalize($reference, $format = null, array $context = []): array
    {
        $normalizedReference = $this->componentNormalizer->normalize($reference, $format, $context);

        $route = $this->router->generate(
            'pim_api_asset_reference_download',
            [
                'code' => $reference->getAsset()->getCode(),
                'localeCode' => $normalizedReference['locale'] ?: AssetReferenceController::NON_LOCALIZABLE_REFERENCE,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $link = new Link('download', $route);

        return array_merge(
            ['_link' => $link->toArray()],
            $normalizedReference
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ReferenceInterface && 'external_api' === $format;
    }
}
