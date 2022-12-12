<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema\Value;

use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema\AssetValueValidatorInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema\Value\MediaLinkTypeValidator;
use PhpSpec\ObjectBehavior;

class MediaLinkTypeValidatorSpec extends ObjectBehavior
{
    function it_is_a_asset_value_validator()
    {
        $this->shouldImplement(AssetValueValidatorInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MediaLinkTypeValidator::class);
    }

    function it_returns_all_the_errors_of_invalid_asset_values()
    {
        $asset = [
            'values' => [
                'preview' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'data'    => 42
                    ],
                    [
                        'channel' => null,
                    ]
                ],
                'preview_big' => [
                    [
                        'channel' => 'ecommerce',
                        'locale'  => 'en_US',
                        'data'    => 'cyberpunk-big',
                        'foo'     => 'bar',
                    ]
                ]
            ]
        ];

        $errors = $this->validate($asset);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(8);
    }

    function it_returns_an_empty_array_if_all_the_asset_values_are_valid()
    {
        $asset = [
            'values' => [
                'preview' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'data'    => 'cyberpunk-big'
                    ],
                ]
            ]
        ];

        $this->validate($asset)->shouldReturn([]);
    }
}
