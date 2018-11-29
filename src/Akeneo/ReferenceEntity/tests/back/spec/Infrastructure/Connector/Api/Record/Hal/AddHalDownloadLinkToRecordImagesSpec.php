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

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\Hal;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindImageAttributeCodesInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\Hal\AddHalDownloadLinkToRecordImages;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class AddHalDownloadLinkToRecordImagesSpec extends ObjectBehavior
{
    function let(
        Router $router,
        FindImageAttributeCodesInterface $findImageAttributeCodes
    ) {
        $this->beConstructedWith($router, $findImageAttributeCodes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddHalDownloadLinkToRecordImages::class);
    }

    function it_adds_hal_download_links_to_images(
        $router,
        $findImageAttributeCodes
    ) {
        $normalizedRecord = [
            'code'       => 'starck',
            'labels'     => [
                'en_US' => 'Philippe Starck',
            ],
            'values'     => [
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
            'main_image' => 'philippeStarck.jpg',
        ];

        $router->generate(
            'akeneo_reference_entities_media_file_rest_connector_get',
            ['fileCode' => 'philippeStarck.jpg'],
            UrlGeneratorInterface::ABSOLUTE_URL
        )
            ->willReturn('http://localhost/api/rest/v1/reference-entities-media-files/philippeStarck.jpg');

        $router->generate(
            'akeneo_reference_entities_media_file_rest_connector_get',
            ['fileCode' => 'starck-cover.jpg'],
            UrlGeneratorInterface::ABSOLUTE_URL
        )
            ->willReturn('http://localhost/api/rest/v1/reference-entities-media-files/starck-cover.jpg');

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $findImageAttributeCodes->__invoke($referenceEntityIdentifier)->willReturn([
            AttributeCode::fromString('coverphoto')
        ]);

        $expectedNormalizedRecord = [
            'code'       => 'starck',
            'labels'     => [
                'en_US' => 'Philippe Starck',
            ],
            'values'     => [
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
                                'href' => 'http://localhost/api/rest/v1/reference-entities-media-files/starck-cover.jpg'
                            ]
                        ]
                    ],
                ],
            ],
            'main_image' => 'philippeStarck.jpg',
            '_links'     => [
                'main_image_download' => [
                    'href' => 'http://localhost/api/rest/v1/reference-entities-media-files/philippeStarck.jpg'
                ]
            ]
        ];

        $this->__invoke($referenceEntityIdentifier, [$normalizedRecord])->shouldReturn([$expectedNormalizedRecord]);
    }

    function it_does_not_add_hal_download_links_if_there_are_no_images(
        $router,
        $findImageAttributeCodes
    ) {
        $normalizedRecord = [
            'code'       => 'starck',
            'labels'     => [
                'en_US' => 'Philippe Starck',
            ],
            'values'     => [
                'nationality' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'data'    => 'French',
                    ],
                ],
            ],
            'main_image' => null,
        ];

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $findImageAttributeCodes->__invoke($referenceEntityIdentifier)->willReturn([
            AttributeCode::fromString('coverphoto')
        ]);

        $router->generate(Argument::any())->shouldNotBeCalled();

        $this->__invoke($referenceEntityIdentifier, [$normalizedRecord])->shouldReturn([$normalizedRecord]);
    }
}
