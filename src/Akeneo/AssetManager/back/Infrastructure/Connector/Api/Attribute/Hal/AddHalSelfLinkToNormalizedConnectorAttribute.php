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

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\Hal;

use Akeneo\Tool\Component\Api\Hal\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class AddHalSelfLinkToNormalizedConnectorAttribute
{
    private Router $router;

    public function __construct(
        Router $router
    ) {
        $this->router = $router;
    }

    public function __invoke(string $assetFamilyIdentifier, array $normalizedAttribute): array
    {
        $selfUrl = $this->router->generate(
            'akeneo_asset_manager_asset_family_attribute_rest_connector_get',
            [
                'assetFamilyIdentifier' => $assetFamilyIdentifier,
                'code' => $normalizedAttribute['code']
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $selfLink = new Link('self', $selfUrl);
        $normalizedAttribute['_links'] = ($normalizedAttribute['_links'] ?? []) + $selfLink->toArray();

        return $normalizedAttribute;
    }
}
