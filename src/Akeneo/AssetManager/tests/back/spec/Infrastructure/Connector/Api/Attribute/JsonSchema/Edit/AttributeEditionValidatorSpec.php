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
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit\AttributeEditionValidator;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit\ImageAttributeValidator;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit\OptionAttributeValidator;
use PhpSpec\ObjectBehavior;

class AttributeEditionValidatorSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        GetAttributeIdentifierInterface $getAttributeIdentifier
    ) {
        $optionAttribute = OptionAttribute::create(
            AttributeIdentifier::fromString('option'),
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('option'),
            LabelCollection::fromArray(['en_US' => 'Main material']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $imageAttribute = ImageAttribute::create(
            AttributeIdentifier::fromString('photo'),
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('photo'),
            LabelCollection::fromArray(['en_US' => 'Cover Image']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.2'),
            AttributeAllowedExtensions::fromList(['jpg'])
        );

        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::fromString('foo'),
            AssetFamilyIdentifier::fromString('foo'),
            AttributeCode::fromString('main_color'),
            LabelCollection::fromArray(['en_US' => 'Main color', 'fr_FR' => 'Couleur principale']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/\w+/')
        );

        $attributeRepository
            ->getByIdentifier(AttributeIdentifier::fromString('option'))
            ->willReturn($optionAttribute);

        $attributeRepository
            ->getByIdentifier(AttributeIdentifier::fromString('photo'))
            ->willReturn($imageAttribute);

        $attributeRepository
            ->getByIdentifier(AttributeIdentifier::fromString('text'))
            ->willReturn($textAttribute);

        $getAttributeIdentifier->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('photo')
        )->willReturn(AttributeIdentifier::fromString('photo'));

        $getAttributeIdentifier->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('option')
        )->willReturn(AttributeIdentifier::fromString('option'));

        $getAttributeIdentifier->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('text')
        )->willReturn(AttributeIdentifier::fromString('text'));

        $this->beConstructedWith(
            $attributeRepository,
            $getAttributeIdentifier,
            [new ImageAttributeValidator(), new OptionAttributeValidator()]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeEditionValidator::class);
    }

    function it_validates_an_image_attribute()
    {
        $this->validate(
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('photo'),
            ['code' => 'photo']
        )->shouldBeArray();
    }

    function it_validates_an_option_attribute()
    {
        $this->validate(
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('option'),
            ['code' => 'option']
        )->shouldBeArray();
    }

    function it_triggers_an_exception_when_no_schema_supported_for_a_given_attribute()
    {
        $this->shouldThrow(\LogicException::class)
            ->during('validate', [
                AssetFamilyIdentifier::fromString('brand'),
                AttributeCode::fromString('text'),
                ['code' => 'starck']
            ]);
    }
}
