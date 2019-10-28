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

use Akeneo\Asset\Component\Model\ReferenceInterface;
use Akeneo\Tool\Component\Api\Hal\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetReferenceNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var RouterInterface */
    private $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($reference, $format = null, array $context = []): array
    {
        $localeCode = $reference->getLocale() ? $reference->getLocale()->getCode() : null;

        if (null === $reference->getFileInfo()) {
            return [
                'locale' => $localeCode,
                'code' => null,
            ];
        }

        $code = $reference->getFileInfo()->getKey();

        $route = $this->router->generate(
            'pimee_api_asset_reference_download',
            [
                'code' => $reference->getAsset()->getCode(),
                'localeCode' => $localeCode ?: 'no-locale'
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $link = new Link('download', $route);

        return array_merge(
            ['_link' => $link->toArray()],
            [
                'locale' => $localeCode,
                'code' => $code,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ReferenceInterface && 'external_api' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
