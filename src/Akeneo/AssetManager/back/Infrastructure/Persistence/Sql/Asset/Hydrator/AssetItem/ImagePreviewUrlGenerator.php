<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem;

use Symfony\Component\Routing\Router;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ImagePreviewUrlGenerator
{
    private const URL_ATTRIBUTE_PREVIEW_ENDPOINT = 'akeneo_asset_manager_image_preview';

    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function generate(string $data, string $attributeIdentifier, string $type): string
    {
        return $this->router->generate(
            self::URL_ATTRIBUTE_PREVIEW_ENDPOINT,
            [
                'data'                => $data,
                'attributeIdentifier' => $attributeIdentifier,
                'type'                => $type
            ]
        );
    }
}
