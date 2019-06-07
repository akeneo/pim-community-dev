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

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\Prefix;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\PreviewType;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\Suffix;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit\AttributeValidatorInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit\UrlAttributeValidator;
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
        $attribute = UrlAttribute::create(
            AttributeIdentifier::create('ad', 'preview', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('ad'),
            AttributeCode::fromString('preview'),
            LabelCollection::fromArray(['en_US' => 'Preview']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::fromString('http://mydam.com'),
            Suffix::fromString(null),
            PreviewType::fromString('image')
        );
        $this->support($attribute)->shouldReturn(true);
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
            'preview_type' => 'image',
            '_links' => [
                'self' => [
                    'href' => 'http://localhost/api/rest/v1/reference-entities/designer/attributes/photo'
                ]
            ],
        ];

        $this->validate($attribute)->shouldReturn([]);
    }

    function it_does_not_return_any_error_when_only_the_code_is_provided()
    {
        $attribute = [
            'code' => 'preview',
        ];

        $this->validate($attribute)->shouldReturn([]);
    }

    function it_is_mandatory_to_provide_the_code_of_the_attribute()
    {
        $attribute = [
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
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_code_is_not_a_string()
    {
        $attribute = [
            'code' => 1,
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
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_channel_is_not_a_boolean()
    {
        $attribute = [
            'code' => 'preview',
            'value_per_channel' => 'foo',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_locale_is_not_a_boolean()
    {
        $attribute = [
            'code' => 'preview',
            'value_per_locale' => 'foo',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_labels_has_a_wrong_format()
    {
        $attribute = [
            'code' => 'preview',
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
            'is_required_for_completeness' => 'foo',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_preview_type_is_null()
    {
        $attribute = [
            'code' => 'preview',
            'preview_type' => null,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_preview_type_is_not_a_string()
    {
        $attribute = [
            'code' => 'preview',
            'preview_type' => 10,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }


    function it_returns_an_error_when_prefix_is_not_a_string_or_null()
    {
        $attribute = [
            'code' => 'preview',
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
            'suffix' => 42,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }
}
