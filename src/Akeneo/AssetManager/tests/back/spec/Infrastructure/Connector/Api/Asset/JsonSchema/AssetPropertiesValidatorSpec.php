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

use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema\AssetPropertiesValidator;
use PhpSpec\ObjectBehavior;

class AssetPropertiesValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AssetPropertiesValidator::class);
    }

    function it_returns_all_the_errors_of_invalid_asset_properties()
    {
        $asset = [
            'values' => null,
            'foo' => 'bar',
        ];

        $errors = $this->validate($asset);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(3);
    }


    function it_returns_an_empty_array_if_all_the_asset_properties_are_valid()
    {
        $asset = [
            'code' => 'starck',
            'values' => [
                'favorite_color' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => 'blue'
                    ],
                ],
            ],
        ];

        $this->validate($asset)->shouldReturn([]);
    }

    function it_accepts_links_in_order_to_update_a_asset_previously_requested_with_the_api()
    {
        $asset = [
            'code' => 'starck',
            '_links' => [
                'self' => [
                    'href' => 'http://localhost:8082/api/rest/v1/asset-families/ref_test_2/assets/0000747832346'
                ]
            ]
        ];

        $this->validate($asset)->shouldReturn([]);
    }
}
