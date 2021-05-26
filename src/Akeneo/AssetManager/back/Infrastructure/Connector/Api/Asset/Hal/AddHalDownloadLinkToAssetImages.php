<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\Hal;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\FindMediaFileAttributeCodesInterface;
use Akeneo\Tool\Component\Api\Hal\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

/**
 * Add download links at HAL format to a list of normalized assets for each asset image (as main media or as value)
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AddHalDownloadLinkToAssetImages
{
    private Router $router;

    private FindMediaFileAttributeCodesInterface $findMediaFileAttributeCodes;

    public function __construct(
        Router $router,
        FindMediaFileAttributeCodesInterface $findMediaFileAttributeCodes
    ) {
        $this->router = $router;
        $this->findMediaFileAttributeCodes = $findMediaFileAttributeCodes;
    }

    public function __invoke(AssetFamilyIdentifier $assetFamilyIdentifier, array $normalizedAssets): array
    {
        $mediaFileAttributeCodes = $this->findMediaFileAttributeCodes->find($assetFamilyIdentifier);

        return array_map(fn ($normalizedAsset) => $this->addDownloadLinkToNormalizedAsset($normalizedAsset, $mediaFileAttributeCodes), $normalizedAssets);
    }

    private function addDownloadLinkToNormalizedAsset(array $normalizedAsset, array $mediaFileAttributeCodes): array
    {
        if (is_object($normalizedAsset['values'])) {
            return $normalizedAsset;
        }

        foreach ($mediaFileAttributeCodes as $mediaFileAttributeCode) {
            $mediaFileAttributeCode = (string) $mediaFileAttributeCode;
            if (isset($normalizedAsset['values'][$mediaFileAttributeCode])) {
                $normalizedAsset['values'][$mediaFileAttributeCode] = $this->addDownloadLinksToImageValues(
                    $normalizedAsset['values'][$mediaFileAttributeCode]
                );
            }
        }

        return $normalizedAsset;
    }

    private function addDownloadLinksToImageValues(array $values): array
    {
        return array_map(function (array $value) {
            if (!empty($value['data'])) {
                $url = $this->generateImageUrl($value['data']);
                $link = new Link('download', $url);
                $value['_links'] = $link->toArray();
            }
            return $value;
        }, $values);
    }

    private function generateImageUrl(string $imageCode): string
    {
        return $this->router->generate(
            'akeneo_asset_manager_media_file_rest_connector_download',
            ['fileCode' => $imageCode],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
