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

namespace spec\Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeLimit;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit\AttributeValidatorInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit\NumberAttributeValidator;
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

    function it_is_json_schema_of_text_attribute()
    {
        $attribute = NumberAttribute::create(
            AttributeIdentifier::fromString('view_number'),
            AssetFamilyIdentifier::fromString('foo'),
            AttributeCode::fromString('view_number'),
            LabelCollection::fromArray(['en_US' => 'View number']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeDecimalsAllowed::fromBoolean(false),
            AttributeLimit::fromString('10'),
            AttributeLimit::limitless()
        );
        $this->support($attribute)->shouldReturn(true);
    }

    function it_does_not_return_any_error_when_the_attribute_is_valid()
    {
        $attribute = [
            'code' => 'view_number',
            'type' => 'number',
            'value_per_channel' => false,
            'value_per_locale' => true,
            'labels' => [
                'en_US' => 'View number'
            ],
            'is_required_for_completeness' => true,
            'decimals_allowed' => false,
            'min_value' => 10,
            'max_value' => null,
            '_links' => [
                'self' => [
                    'href' => 'http://localhost/api/rest/v1/asset-families/packshot/attributes/view_number'
                ]
            ],
        ];

        $this->validate($attribute)->shouldReturn([]);
    }

    function it_does_not_return_any_error_without_additional_values()
    {
        $attribute = [
            'code' => 'view_number',
            'type' => 'number',
            'value_per_channel' => false,
            'value_per_locale' => true,
            'labels' => [
                'en_US' => 'View number'
            ],
            'is_required_for_completeness' => true,
            '_links' => [
                'self' => [
                    'href' => 'http://localhost/api/rest/v1/asset-families/packshot/attributes/view_number'
                ]
            ],
        ];

        $this->validate($attribute)->shouldReturn([]);
    }

    function it_does_not_return_any_error_when_only_the_code_is_provided()
    {
        $attribute = [
            'code' => 'view_number',
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
            'code' => 'view_number',
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
