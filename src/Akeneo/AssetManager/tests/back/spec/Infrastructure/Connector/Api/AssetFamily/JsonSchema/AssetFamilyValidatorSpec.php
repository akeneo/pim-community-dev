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

namespace spec\Akeneo\AssetManager\Infrastructure\Connector\Api\AssetFamily\JsonSchema;

use Akeneo\AssetManager\Infrastructure\Connector\Api\AssetFamily\JsonSchema\AssetFamilyValidator;
use PhpSpec\ObjectBehavior;

class AssetFamilyValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AssetFamilyValidator::class);
    }

    function it_does_not_return_any_error_when_the_asset_family_is_valid()
    {
        $assetFamily = [
            'code' => 'starck',
            'labels' => [
                'en_US' => 'Philippe Starck'
            ],
            'image' => 'images/starck.png',
            '_links'  => [
                'image_download' => [
                    'href' => 'http://localhost/api/rest/v1/asset-families-media-files/images/starck.png'
                ]
            ]
        ];

        $this->validate($assetFamily)->shouldReturn([]);
    }

    function it_is_only_mandatory_to_provide_the_code_of_the_asset_family()
    {
        $assetFamily = [
            'code' => 'starck'
        ];

        $this->validate($assetFamily)->shouldReturn([]);
    }

    function it_returns_an_error_when_code_is_not_a_string()
    {
        $assetFamily = [
            'code' => []
        ];

        $errors = $this->validate($assetFamily);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_labels_has_a_wrong_format()
    {
        $assetFamily = [
            'code' => 'starck',
            'labels' => [
                'en_US' => []
            ]
        ];

        $errors = $this->validate($assetFamily);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_code_is_not_provided()
    {
        $errors = $this->validate([]);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_an_additional_property_is_filled()
    {
        $assetFamily = [
            'code' => 'starck',
            'unknown_property' => 'michel'
        ];

        $errors = $this->validate($assetFamily);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_image_is_not_a_string_or_null()
    {
        $assetFamily = [
            'code' => 'starck',
            'image' => 42
        ];

        $errors = $this->validate($assetFamily);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }
}
