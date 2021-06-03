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

namespace spec\Akeneo\AssetManager\Domain\Query\Asset\Connector;

use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\ConnectorAsset;
use PhpSpec\ObjectBehavior;

class ConnectorAssetSpec extends ObjectBehavior
{
    function let()
    {
        $assetCode = AssetCode::fromString('starck');
        $valueCollection = [
            'label' => [
                [
                    'channel' => null,
                    'locale'  => 'en_US',
                    'value'   => 'Starck'
                ],
                [
                    'channel' => null,
                    'locale'  => 'fr_FR',
                    'value'   => 'Starck'
                ]
            ],
            'description' => [
                [
                    'channel'   => 'ecommerce',
                    'locale'    => 'fr_FR',
                    'data'      => '.one value per channel ecommerce / one value per locale fr_FR.',
                ],
                [
                    'channel'   => 'ecommerce',
                    'locale'    => 'en_US',
                    'data'      => '.one value per channel ecommerce / one value per locale en_US.',
                ],
            ],
            'short_description' => [
                [
                    'channel'   => 'ecommerce',
                    'locale'    => 'en_US',
                    'data'      => '.one value per channel ecommerce / one value per locale en_US.',
                ],
            ]
        ];
        $createdAt = new \DateTimeImmutable('@0');
        $updatedAt = new \DateTimeImmutable('@3600');

        $this->beConstructedWith($assetCode, $valueCollection, $createdAt, $updatedAt);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConnectorAsset::class);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([
            'code' => 'starck',
            'values' => [
                'label' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'value'   => 'Starck'
                    ],
                    [
                        'channel' => null,
                        'locale'  => 'fr_FR',
                        'value'   => 'Starck'
                    ]
                ],
                'description' => [
                    [
                        'channel'   => 'ecommerce',
                        'locale'    => 'fr_FR',
                        'data'      => '.one value per channel ecommerce / one value per locale fr_FR.',
                    ],
                    [
                        'channel'   => 'ecommerce',
                        'locale'    => 'en_US',
                        'data'      => '.one value per channel ecommerce / one value per locale en_US.',
                    ],
                ],
                'short_description' => [
                    [
                        'channel'   => 'ecommerce',
                        'locale'    => 'en_US',
                        'data'      => '.one value per channel ecommerce / one value per locale en_US.',
                    ],
                ]
            ],
            'created' => '1970-01-01T00:00:00+00:00',
            'updated' => '1970-01-01T01:00:00+00:00',
        ]);
    }

    function it_returns_a_asset_with_values_filtered_on_channel()
    {
        $assetCode = AssetCode::fromString('starck');
        $valueCollection = [
            'label' => [
                [
                    'channel' => null,
                    'locale'  => 'en_US',
                    'value'   => 'Starck'
                ],
                [
                    'channel' => null,
                    'locale'  => 'fr_FR',
                    'value'   => 'Starck'
                ]
            ],
            'description' => [
                [
                    'channel'   => 'ecommerce',
                    'locale'    => 'en_US',
                    'data'      => 'Description for e-commerce channel.',
                ],
                [
                    'channel'   => 'tablet',
                    'locale'    => 'en_US',
                    'data'      => 'Description for tablet channel.',
                ],
            ],
            'short_description' => [
                [
                    'channel'   => 'tablet',
                    'locale'    => 'en_US',
                    'data'      => 'Short description for tablet channel.',
                ],
            ],
            'not_scopable_value' => [
                [
                    'channel' => null,
                    'locale'  => 'en_US',
                    'data'    => 'Not scopable value.'
                ]
            ]
        ];
        $createdAt = new \DateTimeImmutable('@0');
        $updatedAt = new \DateTimeImmutable('@3600');

        $this->beConstructedWith($assetCode, $valueCollection, $createdAt, $updatedAt);

        $expectedAsset = new ConnectorAsset(
            $assetCode,
            [
                'label' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'value'   => 'Starck'
                    ],
                    [
                        'channel' => null,
                        'locale'  => 'fr_FR',
                        'value'   => 'Starck'
                    ]
                ],
                'description' => [
                    [
                        'channel' => 'ecommerce',
                        'locale'  => 'en_US',
                        'data'    => 'Description for e-commerce channel.',
                    ],
                ],
                'not_scopable_value' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'data'    => 'Not scopable value.'
                    ]
                ]
            ],
            $createdAt,
            $updatedAt,
        );

        $this->getAssetWithValuesFilteredOnChannel(ChannelIdentifier::fromCode('ecommerce'))->shouldBeLike($expectedAsset);
    }

    function it_filters_values_and_labels_by_locales()
    {
        $assetCode = AssetCode::fromString('starck');
        $valueCollection = [
            'label' => [
                [
                    'channel' => null,
                    'locale'  => 'en_US',
                    'value'   => 'English Starck label'
                ],
                [
                    'channel' => null,
                    'locale'  => 'de_DE',
                    'value'   => 'German Starck label'
                ],
                [
                    'channel' => null,
                    'locale'  => 'fr_FR',
                    'value'   => 'French Starck label'
                ]
            ],
            'description' => [
                [
                    'channel'   => 'ecommerce',
                    'locale'    => 'en_US',
                    'data'      => 'English description.',
                ],
                [
                    'channel'   => 'ecommerce',
                    'locale'    => 'fr_FR',
                    'data'      => 'French description.',
                ],
                [
                    'channel'   => 'ecommerce',
                    'locale'    => 'de_DE',
                    'data'      => 'German description.',
                ],
            ],
            'short_description' => [
                [
                    'channel'   => 'tablet',
                    'locale'    => 'fr_FR',
                    'data'      => 'French short description.',
                ],
            ],
            'not_localizable_value' => [
                [
                    'channel' => 'ecommerce',
                    'locale'  => null,
                    'data'    => 'Not localizable value.'
                ]
            ]
        ];
        $createdAt = new \DateTimeImmutable('@0');
        $updatedAt = new \DateTimeImmutable('@3600');

        $this->beConstructedWith($assetCode, $valueCollection, $createdAt, $updatedAt);

        $expectedAsset = new ConnectorAsset(
            $assetCode,
            [
                'label' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'value'   => 'English Starck label'
                    ],
                    [
                        'channel' => null,
                        'locale'  => 'de_DE',
                        'value'   => 'German Starck label'
                    ],
                ],
                'description' => [
                    [
                        'channel' => 'ecommerce',
                        'locale'  => 'en_US',
                        'data'    => 'English description.',
                    ],
                    [
                        'channel'   => 'ecommerce',
                        'locale'    => 'de_DE',
                        'data'      => 'German description.',
                    ],
                ],
                'not_localizable_value' => [
                    [
                        'channel' => 'ecommerce',
                        'locale'  => null,
                        'data'    => 'Not localizable value.'
                    ],
                ],
            ],
            $createdAt,
            $updatedAt,
        );

        $this->getAssetWithValuesFilteredOnLocales(LocaleIdentifierCollection::fromNormalized([
            'en_US',
            'de_DE',
        ]))->shouldBeLike($expectedAsset);
    }
}
