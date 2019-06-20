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

namespace spec\Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\Hal;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\FindImageAttributeCodesInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\Hal\AddHalDownloadLinkToAssetImages;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class AddHalDownloadLinkToAssetImagesSpec extends ObjectBehavior
{
    function let(
        Router $router,
        FindImageAttributeCodesInterface $findImageAttributeCodes
    ) {
        $this->beConstructedWith($router, $findImageAttributeCodes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddHalDownloadLinkToAssetImages::class);
    }

    function it_adds_hal_download_links_to_images(
        Router $router,
        FindImageAttributeCodesInterface $findImageAttributeCodes
    ) {
        $normalizedAsset = [
            'code'       => 'starck',
            'values'     => [
                'label' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'data'    => 'Philippe Starck',
                    ]
                ],
                'nationality' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'data'    => 'French',
                    ],
                ],
                'birthdate'   => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => '12',
                    ],
                ],
                'coverphoto'  => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 'starck-cover.jpg',
                    ],
                ],
            ],
        ];

        $router->generate(
            'akeneo_asset_manager_media_file_rest_connector_download',
            ['fileCode' => 'starck-cover.jpg'],
            UrlGeneratorInterface::ABSOLUTE_URL
        )
            ->willReturn('http://localhost/api/rest/v1/asset-families-media-files/starck-cover.jpg');

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $findImageAttributeCodes->find($assetFamilyIdentifier)->willReturn([
            AttributeCode::fromString('coverphoto')
        ]);

        $expectedNormalizedAsset = [
            'code'       => 'starck',
            'values'     => [
                'label' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'data'    => 'Philippe Starck',
                    ]
                ],
                'nationality' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'data'    => 'French',
                    ],
                ],
                'birthdate'   => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => '12',
                    ],
                ],
                'coverphoto'  => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 'starck-cover.jpg',
                        '_links'  => [
                            'download' => [
                                'href' => 'http://localhost/api/rest/v1/asset-families-media-files/starck-cover.jpg'
                            ]
                        ]
                    ],
                ],
            ]
        ];

        $this->__invoke($assetFamilyIdentifier, [$normalizedAsset])->shouldReturn([$expectedNormalizedAsset]);
    }

    function it_does_not_add_hal_download_links_if_there_are_no_images(
        Router $router,
        FindImageAttributeCodesInterface $findImageAttributeCodes
    ) {
        $normalizedAsset = [
            'code'       => 'starck',
            'values'     => [
                'label' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'data'    => 'Philippe Starck',
                    ]
                ],
                'nationality' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'data'    => 'French',
                    ],
                ],
            ],
        ];

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $findImageAttributeCodes->find($assetFamilyIdentifier)->willReturn([
            AttributeCode::fromString('coverphoto')
        ]);

        $router->generate(Argument::any())->shouldNotBeCalled();

        $this->__invoke($assetFamilyIdentifier, [$normalizedAsset])->shouldReturn([$normalizedAsset]);
    }

    function it_does_not_add_hal_links_if_values_is_an_empty_object(
        FindImageAttributeCodesInterface $findImageAttributeCodes
    ) {
        $normalizedAsset = [
            'code'       => 'starck',
            'values'     => (object) [],
        ];

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $findImageAttributeCodes->find($assetFamilyIdentifier)->willReturn([
            AttributeCode::fromString('coverphoto')
        ]);


        $this->__invoke($assetFamilyIdentifier, [$normalizedAsset])->shouldReturn([$normalizedAsset]);
    }
}
