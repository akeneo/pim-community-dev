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
use Akeneo\AssetManager\Domain\Query\Attribute\FindImageAttributeCodesInterface;
use Akeneo\Tool\Component\Api\Hal\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

/**
 * Add download links at HAL format to a list of normalized assets for each asset image (as main image or as value)
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AddHalDownloadLinkToAssetImages
{
    /** @var Router */
    private $router;

    /** @var FindImageAttributeCodesInterface */
    private $findImageAttributeCodes;

    public function __construct(
        Router $router,
        FindImageAttributeCodesInterface $findImageAttributeCodes
    ) {
        $this->router = $router;
        $this->findImageAttributeCodes = $findImageAttributeCodes;
    }

    public function __invoke(AssetFamilyIdentifier $assetFamilyIdentifier, array $normalizedAssets): array
    {
        $imageAttributeCodes = $this->findImageAttributeCodes->find($assetFamilyIdentifier);

        return array_map(function ($normalizedAsset) use ($imageAttributeCodes) {
            return $this->addDownloadLinkToNormalizedAsset($normalizedAsset, $imageAttributeCodes);
        }, $normalizedAssets);
    }

    private function addDownloadLinkToNormalizedAsset(array $normalizedAsset, array $imageAttributeCodes): array
    {
        if (is_object($normalizedAsset['values'])) {
            return $normalizedAsset;
        }

        foreach ($imageAttributeCodes as $imageAttributeCode) {
            $imageAttributeCode = (string) $imageAttributeCode;
            if (isset($normalizedAsset['values'][$imageAttributeCode])) {
                $normalizedAsset['values'][$imageAttributeCode] = $this->addDownloadLinksToImageValues(
                    $normalizedAsset['values'][$imageAttributeCode]
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
