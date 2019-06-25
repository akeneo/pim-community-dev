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

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\AssetFamily\Hal;

use Akeneo\Tool\Component\Api\Hal\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class AddHalDownloadLinkToAssetFamilyImage
{
    /** @var Router */
    private $router;

    public function __construct(
        Router $router
    ) {
        $this->router = $router;
    }

    public function __invoke(array $normalizedAssetFamily): array
    {
        if (!empty($normalizedAssetFamily['image'])) {
            $imageMediaLink = $this->generateImageMediaLink($normalizedAssetFamily['image']);
            $imageLink = new Link('image_download', $imageMediaLink);
            $normalizedAssetFamily['_links'] = ($normalizedAssetFamily['_links'] ?? []) + $imageLink->toArray();
        }

        return $normalizedAssetFamily;
    }

    private function generateImageMediaLink(string $imageCode): string
    {
        return $this->router->generate(
            'akeneo_asset_manager_media_file_rest_connector_download',
            ['fileCode' => $imageCode],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
