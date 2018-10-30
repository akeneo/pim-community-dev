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

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Http\Hal;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Http\Hal\AddHalDownloadLinkToImages;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class AddHalDownloadLinkToImagesSpec extends ObjectBehavior
{
    function let(
        Router $router,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->beConstructedWith($router, $findAttributesIndexedByIdentifier);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddHalDownloadLinkToImages::class);
    }

    function it_adds_hal_links_to_image_values(
        $router,
        $findAttributesIndexedByIdentifier,
        TextAttribute $textAttribute,
        ImageAttribute $imageAttribute
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
            'akeneo_reference_entities_file_rest_connector_download',
            ['fileCode' => 'philippeStarck.jpg'],
            UrlGeneratorInterface::ABSOLUTE_URL
        )
            ->willReturn('http://localhost/api/rest/v1/reference-entities-files/philippeStarck.jpg/download');

        $router->generate(
            'akeneo_reference_entities_file_rest_connector_download',
            ['fileCode' => 'starck-cover.jpg'],
            UrlGeneratorInterface::ABSOLUTE_URL
        )
            ->willReturn('http://localhost/api/rest/v1/reference-entities-files/starck-cover.jpg/download');

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $findAttributesIndexedByIdentifier->__invoke($referenceEntityIdentifier)->willReturn([
            'name_designer_fingerprint' => $textAttribute,
            'coverphoto_designer_fingerprint' => $imageAttribute,
        ]);

        $textAttribute->getCode()->willReturn(AttributeCode::fromString('name'));
        $imageAttribute->getCode()->willReturn(AttributeCode::fromString('coverphoto'));

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
                                'href' => 'http://localhost/api/rest/v1/reference-entities-files/starck-cover.jpg/download'
                            ]
                        ]
                    ],
                ],
            ],
            'main_image' => 'philippeStarck.jpg',
            '_links'     => [
                'main_image_download' => [
                    'href' => 'http://localhost/api/rest/v1/reference-entities-files/philippeStarck.jpg/download'
                ]
            ]
        ];

        $this->__invoke($referenceEntityIdentifier, $normalizedRecord)->shouldReturn($expectedNormalizedRecord);
    }

    function it_does_not_add_hal_links_if_there_are_no_images(
        $router,
        $findAttributesIndexedByIdentifier,
        TextAttribute $textAttribute,
        ImageAttribute $imageAttribute
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

        $findAttributesIndexedByIdentifier->__invoke($referenceEntityIdentifier)->willReturn([
            'name_designer_fingerprint' => $textAttribute,
            'coverphoto_designer_fingerprint' => $imageAttribute,
        ]);

        $textAttribute->getCode()->willReturn(AttributeCode::fromString('name'));
        $imageAttribute->getCode()->willReturn(AttributeCode::fromString('coverphoto'));

        $router->generate(Argument::any())->shouldNotBeCalled();

        $this->__invoke($referenceEntityIdentifier, $normalizedRecord)->shouldReturn($normalizedRecord);
    }
}
