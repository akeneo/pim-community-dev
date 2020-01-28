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

namespace spec\Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Create;

use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Create\AssetAttributeValidator;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Create\AttributeValidatorInterface;
use PhpSpec\ObjectBehavior;

class AssetAttributeValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AssetAttributeValidator::class);
    }

    function it_is_an_attribute_validator()
    {
        $this->shouldImplement(AttributeValidatorInterface::class);
    }

    function it_is_json_schema_of_asset_attribute()
    {
        $this->forAttributeTypes()->shouldContain('asset_family_single_link');
    }

    function it_is_json_schema_of_asset_collection_attribute()
    {
        $this->forAttributeTypes()->shouldContain('asset_family_multiple_links');
    }

    function it_does_not_return_any_error_when_the_attribute_is_valid()
    {
        $attribute = [
            'code' => 'starck',
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'labels' => [
                'en_US' => 'Philippe Starck'
            ],
            'is_required_for_completeness' => false,
            'asset_family_code' => 'brand',
        ];

        $this->validate($attribute)->shouldReturn([]);
    }

    function it_does_not_return_any_error_when_no_labels()
    {
        $attribute = [
            'code' => 'starck',
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'labels' => [],
            'is_required_for_completeness' => false,
            'asset_family_code' => 'brand',
        ];

        $this->validate($attribute)->shouldReturn([]);
    }

    function it_can_be_created_without_channel()
    {
        $attribute = [
            'code' => 'starck',
            'type' => 'text',
            'value_per_locale' => true,
            'asset_family_code' => 'brand',
        ];

        $this->validate($attribute)->shouldReturn([]);
    }

    function it_can_be_created_without_locale()
    {
        $attribute = [
            'code' => 'starck',
            'type' => 'text',
            'value_per_channel' => true,
            'asset_family_code' => 'brand',
        ];

        $this->validate($attribute)->shouldReturn([]);
    }

    function it_is_mandatory_to_provide_the_code_of_the_attribute()
    {
        $attribute = [
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'asset_family_code' => 'brand',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_is_mandatory_to_provide_the_type_of_the_attribute()
    {
        $attribute = [
            'code' => 'starck',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'asset_family_code' => 'brand',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_is_mandatory_to_provide_the_asset_family_code_of_the_attribute()
    {
        $attribute = [
            'code' => 'starck',
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'asset_family_code' => 1,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_an_additional_property_is_filled()
    {
        $attribute = [
            'unknown_property' => 'michel',
            'code' => 'starck',
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'asset_family_code' => 'foo',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_code_is_not_a_string()
    {
        $attribute = [
            'code' => 1,
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'asset_family_code' => 'foo',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_type_is_not_a_string()
    {
        $attribute = [
            'code' => 'starck',
            'type' => 1,
            'value_per_channel' => true,
            'value_per_locale' => true,
            'asset_family_code' => 'foo',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_channel_is_not_a_boolean()
    {
        $attribute = [
            'code' => 'starck',
            'type' => 'text',
            'value_per_channel' => 'foo',
            'value_per_locale' => true,
            'asset_family_code' => 'foo',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_locale_is_not_a_boolean()
    {
        $attribute = [
            'code' => 'starck',
            'type' => 'text',
            'value_per_channel' => false,
            'value_per_locale' => 'foo',
            'asset_family_code' => 'foo',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_labels_has_a_wrong_format()
    {
        $attribute = [
            'code' => 'starck',
            'type' => 'text',
            'value_per_channel' => false,
            'value_per_locale' => false,
            'asset_family_code' => 'foo',
            'labels' => [
                'en_US' => []
            ]
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_asset_family_code_is_not_a_string()
    {
        $attribute = [
            'code' => 'starck',
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'asset_family_code' => 1,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }
}
