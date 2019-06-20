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

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema\AssetValuesValidator;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema\AssetValueValidatorInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema\AssetValueValidatorRegistry;
use PhpSpec\ObjectBehavior;

class AssetValuesValidatorSpec extends ObjectBehavior
{
    function let(
        AssetValueValidatorRegistry $assetValueValidatorRegistry,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->beConstructedWith($assetValueValidatorRegistry, $findAttributesIndexedByIdentifier);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetValuesValidator::class);
    }

    function it_validates_asset_values_grouped_by_attribute_type(
        AssetValueValidatorRegistry $assetValueValidatorRegistry,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        AssetValueValidatorInterface $textTypeValidator,
        AssetValueValidatorInterface $assetTypeValidator
    ) {
        $asset = [
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

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand');

        $nameAttribute = $this->getNameAttribute();
        $descriptionAttribute = $this->getDescriptionAttribute();
        $countryAttribute = $this->getCountryAttribute();

        $findAttributesIndexedByIdentifier->find($assetFamilyIdentifier)->willReturn([
            $nameAttribute,
            $descriptionAttribute,
            $countryAttribute,
        ]);

        $assetValueValidatorRegistry->getValidator(TextAttribute::class)->willReturn($textTypeValidator);
        $assetValueValidatorRegistry->getValidator(AssetAttribute::class)->willReturn($assetTypeValidator);

        $textTypeError = [[
            'property' => 'values.description[0].data',
            'message'  => 'The property data is required'
        ]];

        $assetTypeError = [[
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

        $assetTypeValidator->validate([
            'values' => [
                'country' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => 42
                    ],
                ]
            ]
        ])->willReturn([$assetTypeError]);

        $errors = $this->validate($assetFamilyIdentifier, $asset);
        $errors->shouldHaveCount(2);
        $errors->shouldContain($textTypeError);
        $errors->shouldContain($assetTypeError);
    }

    function it_returns_an_empty_array_if_there_are_no_errors(
        AssetValueValidatorRegistry $assetValueValidatorRegistry,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        AssetValueValidatorInterface $textTypeValidator,
        AssetValueValidatorInterface $assetTypeValidator
    ) {
        $asset = [
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

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand');

        $findAttributesIndexedByIdentifier->find($assetFamilyIdentifier)->willReturn([
            $this->getNameAttribute(),
            $this->getDescriptionAttribute(),
            $this->getCountryAttribute(),
            $this->getImageAttribute(),
        ]);

        $assetValueValidatorRegistry->getValidator(TextAttribute::class)->willReturn($textTypeValidator);
        $assetValueValidatorRegistry->getValidator(AssetAttribute::class)->willReturn($assetTypeValidator);

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

        $assetTypeValidator->validate([
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

        $this->validate($assetFamilyIdentifier, $asset)->shouldReturn([]);
    }

    private function getNameAttribute(): TextAttribute
    {
        return TextAttribute::createText(
            AttributeIdentifier::create('brand', 'name', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
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
            AssetFamilyIdentifier::fromString('brand'),
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

    private function getCountryAttribute(): AssetAttribute
    {
        return AssetAttribute::create(
            AttributeIdentifier::create('brand', 'country', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('country'),
            LabelCollection::fromArray(['fr_FR' => 'Pays', 'en_US' => 'Country']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AssetFamilyIdentifier::fromString('country')
        );
    }

    private function getImageAttribute(): ImageAttribute
    {
        return ImageAttribute::create(
            AttributeIdentifier::create('brand', 'cover_image', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
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
