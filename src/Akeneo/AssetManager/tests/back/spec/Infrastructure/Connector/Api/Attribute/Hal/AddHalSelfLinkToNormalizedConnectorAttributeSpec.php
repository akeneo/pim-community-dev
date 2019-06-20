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

namespace spec\Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\Hal;

use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\Hal\AddHalSelfLinkToNormalizedConnectorAttribute;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class AddHalSelfLinkToNormalizedConnectorAttributeSpec extends ObjectBehavior
{
    function let(
        Router $router
    ) {
        $this->beConstructedWith($router);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddHalSelfLinkToNormalizedConnectorAttribute::class);
    }

    function it_adds_hal_download_links_to_images(Router $router)
    {
        $normalizedAttribute = [
            'code' => 'country',
            'labels' => [
                'en_US' => 'Country',
                'fr_FR' => 'Pays'
            ],
            'type' => 'asset_family_single_link',
            'localizable' => true,
            'scopable' => true,
            'is_required_for_completeness' => false,
            'asset_family_code' => 'country'
        ];

        $router->generate(
            'akeneo_asset_manager_asset_family_attribute_rest_connector_get',
            [
                'assetFamilyIdentifier' => 'designer',
                'code' => $normalizedAttribute['code']
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://localhost/api/rest/v1/asset-families/designer/attributes/country');

        $expectedNormalizedAsset = [
            'code' => 'country',
            'labels' => [
                'en_US' => 'Country',
                'fr_FR' => 'Pays'
            ],
            'type' => 'asset_family_single_link',
            'localizable' => true,
            'scopable' => true,
            'is_required_for_completeness' => false,
            'asset_family_code' => 'country',
            '_links'     => [
                'self' => [
                    'href' => 'http://localhost/api/rest/v1/asset-families/designer/attributes/country'
                ]
            ]
        ];

        $this->__invoke('designer', $normalizedAttribute)->shouldReturn($expectedNormalizedAsset);
    }
}
