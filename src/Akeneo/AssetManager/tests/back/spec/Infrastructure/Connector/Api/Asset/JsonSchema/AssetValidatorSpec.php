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

namespace spec\Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema\AssetPropertiesValidator;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema\AssetValidator;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema\AssetValuesValidator;
use PhpSpec\ObjectBehavior;

class AssetValidatorSpec extends ObjectBehavior
{
    function let(AssetPropertiesValidator $assetPropertiesValidator, AssetValuesValidator $assetValuesValidator)
    {
        $this->beConstructedWith($assetPropertiesValidator, $assetValuesValidator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetValidator::class);
    }

    function it_validates_both_properties_and_values_of_a_valid_asset($assetPropertiesValidator, $assetValuesValidator)
    {
        $asset = [
            'code' => 'starck',
            'labels' => [
                'en_US' => 'Philippe Starck'
            ],
            'image' => null,
            'values' => [
                'nationality' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'data'    => 'French'
                    ],
                ],
                'image' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => '2/4/3/7/24378761474c58aeee26016ee881b3b15069de52_starck.jpg',
                        '_links'  => [
                            'download' => [
                                'href' => 'http://localhost/api/rest/v1/asset-families-media-files/2/4/3/7/24378761474c58aeee26016ee881b3b15069de52_kartell_cover.jpg'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $assetPropertiesValidator->validate($asset)->shouldBeCalled()->willReturn([]);
        $assetValuesValidator->validate($assetFamilyIdentifier, $asset)->shouldBeCalled()->willReturn([]);

        $this->validate($assetFamilyIdentifier, $asset)->shouldReturn([]);
    }

    function it_returns_errors_of_invalid_values_if_properties_are_valid($assetPropertiesValidator, $assetValuesValidator)
    {
        $asset = [
            'code' => 'starck',
            'labels' => [
                'en_US' => 'Philippe Starck'
            ],
            'image' => null,
            'values' => [
                'nationality' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'data'    => 42
                    ],
                ]
            ]
        ];

        $errors = [[
            'property' => 'values.nationality[0].data',
            'message'  => 'Integer value found, but a string or a null is required',
        ]];

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $assetPropertiesValidator->validate($asset)->willReturn([]);
        $assetValuesValidator->validate($assetFamilyIdentifier, $asset)->willReturn($errors);

        $this->validate($assetFamilyIdentifier, $asset)->shouldReturn($errors);
    }

    function it_does_not_validate_values_if_the_are_invalid_properties(
        $assetPropertiesValidator,
        $assetValuesValidator
    ) {
        $asset = [
            'code' => 'starck',
            'labels' => [
                'en_US' => 'Philippe Starck'
            ],
            'image' => null,
            'values' => [
                'foo' => 'bar',
                'nationality' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'data'    => 'French'
                    ],
                ]
            ]
        ];

        $errors = [[
            'property' => 'values.foo',
            'message'  => 'String value found, but an array is required',
        ]];

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $assetPropertiesValidator->validate($asset)->shouldBeCalled()->willReturn($errors);
        $assetValuesValidator->validate($assetFamilyIdentifier, $asset)->shouldNotBeCalled();

        $this->validate($assetFamilyIdentifier, $asset)->shouldReturn($errors);
    }
}
