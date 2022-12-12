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

namespace spec\Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Create;

use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Create\AttributeValidatorInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Create\NumberAttributeValidator;
use PhpSpec\ObjectBehavior;

class NumberAttributeValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NumberAttributeValidator::class);
    }

    function it_is_an_attribute_validator()
    {
        $this->shouldImplement(AttributeValidatorInterface::class);
    }

    function it_is_json_schema_of_number_attribute()
    {
        $this->forAttributeTypes()->shouldContain('number');
    }

    function it_does_not_return_any_error_when_the_attribute_is_valid()
    {
        $attribute = [
            'code' => 'view_number',
            'type' => 'number',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'labels' => [
                'en_US' => 'View number'
            ],
            'is_required_for_completeness' => false,
            'decimals_allowed' => false,
            'min_value' => 0,
            'max_value' => null
        ];

        $this->validate($attribute)->shouldReturn([]);
    }

    function it_does_not_return_any_error_without_additional_values()
    {
        $attribute = [
            'code' => 'view_number',
            'type' => 'number',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'labels' => [
                'en_US' => 'View number'
            ],
            'is_required_for_completeness' => false
        ];

        $this->validate($attribute)->shouldReturn([]);
    }

    function it_is_mandatory_to_provide_the_code_of_the_attribute()
    {
        $attribute = [
            'type' => 'number',
            'value_per_channel' => true,
            'value_per_locale' => true,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_is_mandatory_to_provide_the_type_of_the_attribute()
    {
        $attribute = [
            'code' => 'view_number',
            'value_per_channel' => true,
            'value_per_locale' => true,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_an_additional_property_is_filled()
    {
        $attribute = [
            'unknown_property' => 'michel',
            'code' => 'view_number',
            'type' => 'number',
            'value_per_channel' => true,
            'value_per_locale' => true,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_errors_when_code_is_not_a_string()
    {
        $attribute = [
            'code' => 1,
            'type' => 'number',
            'value_per_channel' => true,
            'value_per_locale' => true,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_errors_when_type_is_not_a_string()
    {
        $attribute = [
            'code' => 'view_number',
            'type' => 1,
            'value_per_channel' => true,
            'value_per_locale' => true,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_errors_when_channel_is_not_a_boolean()
    {
        $attribute = [
            'code' => 'view_number',
            'type' => 'number',
            'value_per_channel' => 'foo',
            'value_per_locale' => true,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_errors_when_locale_is_not_a_boolean()
    {
        $attribute = [
            'code' => 'View_number',
            'type' => 'number',
            'value_per_channel' => false,
            'value_per_locale' => 'foo',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_errors_when_labels_has_a_wrong_format()
    {
        $attribute = [
            'code' => 'view_number',
            'type' => 'number',
            'value_per_channel' => false,
            'value_per_locale' => false,
            'labels' => [
                'en_US' => []
            ]
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(3);
    }

    function it_returns_errors_when_is_required_for_completeness_is_not_a_boolean()
    {
        $attribute = [
            'code' => 'view_number',
            'type' => 'number',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'is_required_for_completeness' => 'foo',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_errors_when_decimals_allowed_is_not_a_boolean()
    {
        $attribute = [
            'code' => 'view_number',
            'type' => 'number',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'decimals_allowed' => 'foo',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_errors_when_min_value_is_not_a_string_or_an_integer()
    {
        $attribute = [
            'code' => 'view_number',
            'type' => 'number',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'min_value' => ['10'],
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_errors_when_max_value_is_not_a_string_or_an_integer()
    {
        $attribute = [
            'code' => 'starck',
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'max_value' => ['10'],
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }
}
