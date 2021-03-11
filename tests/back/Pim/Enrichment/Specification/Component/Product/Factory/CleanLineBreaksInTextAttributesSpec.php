<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use PhpSpec\ObjectBehavior;

final class CleanLineBreaksInTextAttributesSpec extends ObjectBehavior
{
    function it_cleans_text_attributes_and_returns_cleaned_fields()
    {
        $rawValueCollections = [
            'movie' => [
                'name' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'Philips SA4TP404KF Tap 4.3 MP3 video player line'.PHP_EOL.'break',
                    ],
                ],
                'title' => [
                    '<all_channels>' => [
                        'fr_FR' => 'Philips SA4TP404KF Tap 4.3 MP3 video player line'.PHP_EOL.'break',
                        'en_US' => "Philips SA4TP404KF Tap 4.3 MP3 video player line\rbreak",
                        'de_DE' => "Philips SA4TP404KF Tap 4.3 MP3 video player linebreak",
                    ],
                ],
                'subtitle' => [
                    'print' => [
                        'en_US' => 'The GoGEAR Tap 4.3 MP3 line'.PHP_EOL.'break',
                        'fr_FR' => "The GoGEAR Tap 4.3 MP3 line\rbreak",
                    ],
                    'ecommerce' => [
                        'en_US' => 'The GoGEAR Tap 4.3 MP3 line'.PHP_EOL.'break',
                        'de_DE' => "The GoGEAR Tap 4.3",
                    ],
                ],
                'comment' => [
                    'print' => [
                        '<all_locales>' => 'The GoGEAR Tap 4.3 MP3 line'.PHP_EOL.'break',
                    ],
                    'ecommerce' => [
                        '<all_locales>' => "The GoGEAR Tap 4.3 MP3 line\rbreak",
                    ],
                ],
                'description' => [
                    'print' => [
                        '<all_locales>' => 'The GoGEAR Tap 4.3 MP3 line'.PHP_EOL.'break',
                    ],
                    'ecommerce' => [
                        '<all_locales>' => "The GoGEAR Tap 4.3 MP3 line\rbreak",
                    ],
                ],
            ]
        ];

        $attributes =
        [
            'name' => $this->buildAttribute('name', AttributeTypes::TEXT),
            'title' => $this->buildAttribute('title', AttributeTypes::TEXT),
            'subtitle' => $this->buildAttribute('subtitle', AttributeTypes::TEXT),
            'comment' => $this->buildAttribute('comment', AttributeTypes::TEXT),
            'description' => $this->buildAttribute('description', AttributeTypes::TEXTAREA),
        ];

        $this->cleanFromRawValuesFormat($rawValueCollections, $attributes)->shouldReturn(['movie' => [
            'name' => [
                '<all_channels>' => [
                    '<all_locales>' => 'Philips SA4TP404KF Tap 4.3 MP3 video player line break',
                ],
            ],
            'title' => [
                '<all_channels>' => [
                    'fr_FR' => 'Philips SA4TP404KF Tap 4.3 MP3 video player line break',
                    'en_US' => "Philips SA4TP404KF Tap 4.3 MP3 video player line break",
                    'de_DE' => "Philips SA4TP404KF Tap 4.3 MP3 video player linebreak",
                ],
            ],
            'subtitle' => [
                'print' => [
                    'en_US' => 'The GoGEAR Tap 4.3 MP3 line break',
                    'fr_FR' => "The GoGEAR Tap 4.3 MP3 line break",
                ],
                'ecommerce' => [
                    'en_US' => 'The GoGEAR Tap 4.3 MP3 line break',
                    'de_DE' => "The GoGEAR Tap 4.3",
                ],
            ],
            'comment' => [
                'print' => [
                    '<all_locales>' => 'The GoGEAR Tap 4.3 MP3 line break',
                ],
                'ecommerce' => [
                    '<all_locales>' => "The GoGEAR Tap 4.3 MP3 line break",
                ],
            ],
            'description' => [
                'print' => [
                    '<all_locales>' => 'The GoGEAR Tap 4.3 MP3 line'.PHP_EOL.'break',
                ],
                'ecommerce' => [
                    '<all_locales>' => "The GoGEAR Tap 4.3 MP3 line\rbreak",
                ],
            ],
        ]]);
    }

    function it_cleans_several_sort_of_line_breaks()
    {
        $rawValueCollections = [
            'movie' => [
                'name' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'Philips SA4TP404KF Tap 4.3 MP3 video player line'.PHP_EOL.'break',
                    ],
                ],
                'title' => [
                    '<all_channels>' => [
                        'en_US' => "Philips SA4TP404KF Tap 4.3 MP3 video player line\rbreak",
                    ],
                ],
                'subtitle' => [
                    'print' => [
                        'en_US' => 'The GoGEAR Tap 4.3 MP3 line'.PHP_EOL.PHP_EOL.PHP_EOL.'break',
                    ],
                ],
                'comment' => [
                    'ecommerce' => [
                        '<all_locales>' => "The GoGEAR Tap 4.3 MP3 line\r\nbreak",
                    ],
                ],
            ]
        ];

        $attributes =
            [
                'name' => $this->buildAttribute('name', AttributeTypes::TEXT),
                'title' => $this->buildAttribute('title', AttributeTypes::TEXT),
                'subtitle' => $this->buildAttribute('subtitle', AttributeTypes::TEXT),
                'comment' => $this->buildAttribute('comment', AttributeTypes::TEXT),
            ];

        $this->cleanFromRawValuesFormat($rawValueCollections, $attributes)->shouldReturn(['movie' => [
            'name' => [
                '<all_channels>' => [
                    '<all_locales>' => 'Philips SA4TP404KF Tap 4.3 MP3 video player line break',
                ],
            ],
            'title' => [
                '<all_channels>' => [
                    'en_US' => "Philips SA4TP404KF Tap 4.3 MP3 video player line break",
                ],
            ],
            'subtitle' => [
                'print' => [
                    'en_US' => 'The GoGEAR Tap 4.3 MP3 line break',
                ],
            ],
            'comment' => [
                'ecommerce' => [
                    '<all_locales>' => "The GoGEAR Tap 4.3 MP3 line break",
                ],
            ],
        ]]);
    }

    private function buildAttribute(string $code, string $type): Attribute
    {
        return new Attribute(
            $code,
            $type,
            [],
            true,
            true,
            null,
            null,
            null,
            '',
            []
        );
    }
}
