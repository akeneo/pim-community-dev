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
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\Create\UrlAttributeValidator;
use PhpSpec\ObjectBehavior;

class UrlAttributeValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UrlAttributeValidator::class);
    }

    function it_is_an_attribute_validator()
    {
        $this->shouldImplement(AttributeValidatorInterface::class);
    }

    function it_is_json_schema_of_url_attribute()
    {
        $this->forAttributeTypes()->shouldContain('url');
    }

    function it_does_not_return_any_error_when_the_attribute_is_valid()
    {
        $attribute = [
            'code' => 'preview',
            'type' => 'url',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'labels' => [
                'en_US' => 'Preview'
            ],
            'is_required_for_completeness' => false,
            'media_type' => 'image',
            'prefix' => 'http://mydam.com/pic/',
            'suffix' => null,
        ];

        $this->validate($attribute)->shouldReturn([]);
    }

    function it_is_mandatory_to_provide_the_code_of_the_attribute()
    {
        $attribute = [
            'type' => 'url',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'media_type' => 'image',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_is_mandatory_to_provide_the_channel_of_the_attribute()
    {
        $attribute = [
            'code' => 'preview',
            'type' => 'url',
            'value_per_locale' => true,
            'media_type' => 'image',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_is_mandatory_to_provide_the_locale_of_the_attribute()
    {
        $attribute = [
            'code' => 'preview',
            'type' => 'url',
            'value_per_channel' => true,
            'media_type' => 'image',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_is_mandatory_to_provide_the_type_of_the_attribute()
    {
        $attribute = [
            'code' => 'preview',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'media_type' => 'image',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_is_mandatory_to_provide_the_media_type_of_the_attribute()
    {
        $attribute = [
            'code' => 'preview',
            'type' => 'url',
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
            'code' => 'preview',
            'type' => 'url',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'media_type' => 'image',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_code_is_not_a_string()
    {
        $attribute = [
            'code' => 1,
            'type' => 'url',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'media_type' => 'image',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_type_is_not_a_string()
    {
        $attribute = [
            'code' => 'preview',
            'type' => 1,
            'value_per_channel' => true,
            'value_per_locale' => true,
            'media_type' => 'image',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_channel_is_not_a_boolean()
    {
        $attribute = [
            'code' => 'preview',
            'type' => 'url',
            'value_per_channel' => 'oui',
            'value_per_locale' => true,
            'media_type' => 'image',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_locale_is_not_a_boolean()
    {
        $attribute = [
            'code' => 'preview',
            'type' => 'url',
            'value_per_channel' => false,
            'value_per_locale' => 'mais tout à fait',
            'media_type' => 'image',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_labels_has_a_wrong_format()
    {
        $attribute = [
            'code' => 'preview',
            'type' => 'url',
            'value_per_channel' => false,
            'value_per_locale' => false,
            'media_type' => 'image',
            'labels' => [
                'en_US' => []
            ]
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_is_required_for_completeness_is_not_a_boolean()
    {
        $attribute = [
            'code' => 'preview',
            'type' => 'url',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'media_type' => 'image',
            'is_required_for_completeness' => 'foo',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_media_type_is_null()
    {
        $attribute = [
            'code' => 'preview',
            'type' => 'url',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'media_type' => null,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_media_type_is_not_a_string()
    {
        $attribute = [
            'code' => 'preview',
            'type' => 'url',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'media_type' => 14,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_prefix_is_not_a_string_or_null()
    {
        $attribute = [
            'code' => 'preview',
            'type' => 'url',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'media_type' => 'image',
            'prefix' => 42,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_suffix_is_not_a_string_or_null()
    {
        $attribute = [
            'code' => 'preview',
            'type' => 'url',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'media_type' => 'image',
            'suffix' => 42,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }
}
