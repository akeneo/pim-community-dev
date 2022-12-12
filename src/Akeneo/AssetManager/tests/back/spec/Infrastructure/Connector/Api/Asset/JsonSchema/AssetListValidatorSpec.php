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

use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema\AssetListValidator;
use PhpSpec\ObjectBehavior;

class AssetListValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AssetListValidator::class);
    }

    function it_returns_the_errors_of_an_invalid_list_of_assets()
    {
        $assetList = [
            [
                'not a object'
            ],
            'not an array'
        ];

        $errors = $this->validate($assetList);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(3);
    }

    function it_returns_an_empty_array_if_the_list_of_assets_is_valid()
    {
        $assetList = [
            [
                'code' => 'starck',
                'labels' => [
                    'en_US' => 'Philippe Starck',
                ],
            ],
            [
                'code' => 'dyson',
                'values' => [],
            ]
        ];

        $this->validate($assetList)->shouldBe([]);
    }
}
