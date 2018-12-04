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

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\RecordValuesValidator;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\RecordValueValidatorInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\RecordValueValidatorRegistry;
use PhpSpec\ObjectBehavior;

class RecordValuesValidatorSpec extends ObjectBehavior
{
    function let(
        RecordValueValidatorRegistry $recordValueValidatorRegistry,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->beConstructedWith($recordValueValidatorRegistry, $findAttributesIndexedByIdentifier);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordValuesValidator::class);
    }

    function it_validates_record_values_grouped_by_attribute_type(
        $recordValueValidatorRegistry,
        $findAttributesIndexedByIdentifier,
        RecordValueValidatorInterface $textTypeValidator,
        RecordValueValidatorInterface $recordTypeValidator
    ) {
        $record = [
            'values' => [
                'name' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'data'    => 'Kartell'
                    ]
                ],
                'description' => [
                    [
                        'channel' => 'ecommerce',
                        'locale'  => 'en_US',
                    ]
                ],
                'country' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => 42
                    ],
                ]
            ]
        ];

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');

        $nameAttribute = $this->getNameAttribute();
        $descriptionAttribute = $this->getDescriptionAttribute();
        $countryAttribute = $this->getCountryAttribute();

        $findAttributesIndexedByIdentifier->__invoke($referenceEntityIdentifier)->willReturn([
            $nameAttribute,
            $descriptionAttribute,
            $countryAttribute,
        ]);

        $recordValueValidatorRegistry->getValidator(TextAttribute::class)->willReturn($textTypeValidator);
        $recordValueValidatorRegistry->getValidator(RecordAttribute::class)->willReturn($recordTypeValidator);

        $textTypeError = [[
            'property' => 'values.description[0].data',
            'message'  => 'The property data is required'
        ]];

        $recordTypeError = [[
            'property' => 'values.country[0].data',
            'message'  => 'Integer value found, but a string or a null is required'
        ]];

        $textTypeValidator->validate([
            'values' => [
                'name' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'data'    => 'Kartell'
                    ]
                ],
                'description' => [
                    [
                        'channel' => 'ecommerce',
                        'locale'  => 'en_US',
                    ]
                ],
            ]
        ])->willReturn([$textTypeError]);

        $recordTypeValidator->validate([
            'values' => [
                'country' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => 42
                    ],
                ]
            ]
        ])->willReturn([$recordTypeError]);

        $errors = $this->validate($referenceEntityIdentifier, $record);
        $errors->shouldHaveCount(2);
        $errors->shouldContain($textTypeError);
        $errors->shouldContain($recordTypeError);
    }

    function it_returns_an_empty_array_if_there_are_no_errors(
        $recordValueValidatorRegistry,
        $findAttributesIndexedByIdentifier,
        RecordValueValidatorInterface $textTypeValidator,
        RecordValueValidatorInterface $recordTypeValidator
    ) {
        $record = [
            'values' => [
                'name' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'data'    => 'Kartell'
                    ]
                ],
                'description' => [
                    [
                        'channel' => 'ecommerce',
                        'locale'  => 'en_US',
                        'data'    => 'The Kartell company'
                    ]
                ],
                'country' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => 'italy'
                    ],
                ]
            ]
        ];

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');

        $findAttributesIndexedByIdentifier->__invoke($referenceEntityIdentifier)->willReturn([
            $this->getNameAttribute(),
            $this->getDescriptionAttribute(),
            $this->getCountryAttribute(),
            $this->getImageAttribute(),
        ]);

        $recordValueValidatorRegistry->getValidator(TextAttribute::class)->willReturn($textTypeValidator);
        $recordValueValidatorRegistry->getValidator(RecordAttribute::class)->willReturn($recordTypeValidator);

        $textTypeValidator->validate([
            'values' => [
                'name' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'data'    => 'Kartell'
                    ]
                ],
                'description' => [
                    [
                        'channel' => 'ecommerce',
                        'locale'  => 'en_US',
                        'data'    => 'The Kartell company'
                    ]
                ],
            ]
        ])->willReturn([]);

        $recordTypeValidator->validate([
            'values' => [
                'country' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => 'italy'
                    ],
                ]
            ]
        ])->willReturn([]);

        $this->validate($referenceEntityIdentifier, $record)->shouldReturn([]);
    }

    private function getNameAttribute(): TextAttribute
    {
        return TextAttribute::createText(
            AttributeIdentifier::create('brand', 'name', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
    }

    private function getDescriptionAttribute(): TextAttribute
    {
        return TextAttribute::createText(
            AttributeIdentifier::create('brand', 'description', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
    }

    private function getCountryAttribute(): RecordAttribute
    {
        return RecordAttribute::create(
            AttributeIdentifier::create('brand', 'country', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('country'),
            LabelCollection::fromArray(['fr_FR' => 'Pays', 'en_US' => 'Country']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('country')
        );
    }

    private function getImageAttribute(): ImageAttribute
    {
        return ImageAttribute::create(
            AttributeIdentifier::create('brand', 'cover_image', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('cover_image'),
            LabelCollection::fromArray(['en_US' => 'Cover Image']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.2'),
            AttributeAllowedExtensions::fromList(['jpg'])
        );
    }
}
