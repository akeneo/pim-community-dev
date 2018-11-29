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

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Hal;

use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Hal\AddHalDownloadLinkToReferenceEntityImage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class AddHalDownloadLinkToReferenceEntityImageSpec extends ObjectBehavior
{
    function let(
        Router $router
    ) {
        $this->beConstructedWith($router);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddHalDownloadLinkToReferenceEntityImage::class);
    }

    function it_adds_hal_download_links_to_reference_entity_image(
        $router
    ) {
        $normalizedReferenceEntity = [
            'code'       => 'brand',
            'labels'     => [
                'en_US' => 'Marque',
            ],
            'image' => 'brand.jpg',
        ];

        $router->generate(
            'akeneo_reference_entities_media_file_rest_connector_get',
            ['fileCode' => 'brand.jpg'],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://localhost/api/rest/v1/reference-entities-media-files/brand.jpg');

        $expectedNormalizedReferenceEntity = [
            'code'       => 'brand',
            'labels'     => [
                'en_US' => 'Marque',
            ],
            'image' => 'brand.jpg',
            '_links'     => [
                'image_download' => [
                    'href' => 'http://localhost/api/rest/v1/reference-entities-media-files/brand.jpg'
                ]
            ]
        ];

        $this->__invoke($normalizedReferenceEntity)->shouldReturn($expectedNormalizedReferenceEntity);
    }

    function it_does_not_add_hal_download_links_if_there_is_no_image(
        $router
    ) {
        $normalizedReferenceEntity = [
            'code'       => 'brand',
            'labels'     => [
                'en_US' => 'Marque',
            ],
            'image' => null,
        ];

        $router->generate(Argument::any())->shouldNotBeCalled();

        $this->__invoke($normalizedReferenceEntity)->shouldReturn($normalizedReferenceEntity);
    }
}
