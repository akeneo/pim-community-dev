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

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\Create;

use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\Create\AttributeValidatorInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\Create\RecordAttributeValidator;
use PhpSpec\ObjectBehavior;

class RecordAttributeValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RecordAttributeValidator::class);
    }

    function it_is_an_attribute_validator()
    {
        $this->shouldImplement(AttributeValidatorInterface::class);
    }

    function it_is_json_schema_of_record_attribute()
    {
        $this->forAttributeTypes()->shouldContain('reference_entity_single_link');
    }

    function it_is_json_schema_of_record_collection_attribute()
    {
        $this->forAttributeTypes()->shouldContain('reference_entity_multiple_links');
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
            'reference_entity_code' => 'brand',
        ];

        $this->validate($attribute)->shouldReturn([]);
    }

    function it_is_mandatory_to_provide_the_code_of_the_attribute()
    {
        $attribute = [
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'reference_entity_code' => 'brand',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_is_mandatory_to_provide_the_channel_of_the_attribute()
    {
        $attribute = [
            'code' => 'starck',
            'type' => 'text',
            'value_per_locale' => true,
            'reference_entity_code' => 'brand',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_is_mandatory_to_provide_the_locale_of_the_attribute()
    {
        $attribute = [
            'code' => 'starck',
            'type' => 'text',
            'value_per_channel' => true,
            'reference_entity_code' => 'brand',
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
            'reference_entity_code' => 'brand',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_is_mandatory_to_provide_the_reference_entity_code_of_the_attribute()
    {
        $attribute = [
            'code' => 'starck',
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'reference_entity_code' => 1,
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
            'reference_entity_code' => 'foo',
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
            'reference_entity_code' => 'foo',
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
            'reference_entity_code' => 'foo',
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
            'reference_entity_code' => 'foo',
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
            'reference_entity_code' => 'foo',
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
            'reference_entity_code' => 'foo',
            'labels' => [
                'en_US' => []
            ]
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_reference_entity_code_is_not_a_string()
    {
        $attribute = [
            'code' => 'starck',
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'reference_entity_code' => 1,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }
}
