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

namespace spec\Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit\AttributeValidatorInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit\MediaLinkAttributeValidator;
use PhpSpec\ObjectBehavior;

class MediaLinkAttributeValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MediaLinkAttributeValidator::class);
    }

    function it_is_an_attribute_validator()
    {
        $this->shouldImplement(AttributeValidatorInterface::class);
    }

    function it_is_json_schema_of_mediaLink_attribute()
    {
        $attribute = MediaLinkAttribute::create(
            AttributeIdentifier::create('ad', 'preview', 'fingerprint'),
            AssetFamilyIdentifier::fromString('ad'),
            AttributeCode::fromString('preview'),
            LabelCollection::fromArray(['en_US' => 'Preview']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::fromString('http://mydam.com'),
            Suffix::fromString(null),
            MediaType::fromString('image')
        );
        $this->support($attribute)->shouldReturn(true);
    }

    function it_does_not_return_any_error_when_the_attribute_is_valid()
    {
        $attribute = [
            'code' => 'preview',
            'type' => 'media_link',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'labels' => [
                'en_US' => 'Preview'
            ],
            'is_required_for_completeness' => false,
            'media_type' => 'image',
            '_links' => [
                'self' => [
                    'href' => 'http://localhost/api/rest/v1/asset-families/designer/attributes/photo'
                ]
            ],
        ];

        $this->validate($attribute)->shouldReturn([]);
    }

    function it_does_not_return_any_error_when_no_labels()
    {
        $attribute = [
            'code' => 'preview',
            'type' => 'media_link',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'labels' => [],
            'is_required_for_completeness' => false,
            'media_type' => 'image',
            '_links' => [
                'self' => [
                    'href' => 'http://localhost/api/rest/v1/asset-families/designer/attributes/photo'
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
            'type' => 'media_link',
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
            'type' => 'media_link',
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
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_errors_when_type_is_not_a_string()
    {
        $attribute = [
            'code' => 'preview',
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
            'code' => 'preview',
            'value_per_channel' => 'foo',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_errors_when_locale_is_not_a_boolean()
    {
        $attribute = [
            'code' => 'preview',
            'value_per_locale' => 'foo',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_errors_when_labels_has_a_wrong_format()
    {
        $attribute = [
            'code' => 'preview',
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
            'code' => 'preview',
            'is_required_for_completeness' => 'foo',
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_errors_when_media_type_is_null()
    {
        $attribute = [
            'code' => 'preview',
            'media_type' => null,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_errors_when_media_type_is_not_a_string()
    {
        $attribute = [
            'code' => 'preview',
            'media_type' => 10,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_errors_when_prefix_is_not_a_string_or_null()
    {
        $attribute = [
            'code' => 'preview',
            'prefix' => 42,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_errors_when_suffix_is_not_a_string_or_null()
    {
        $attribute = [
            'code' => 'preview',
            'suffix' => 42,
        ];

        $errors = $this->validate($attribute);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }
}
