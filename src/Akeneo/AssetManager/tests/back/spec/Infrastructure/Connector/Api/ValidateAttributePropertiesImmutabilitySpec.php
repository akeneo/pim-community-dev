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

namespace spec\Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\FindConnectorAttributeByIdentifierAndCodeInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\ValidateAttributePropertiesImmutability;
use PhpSpec\ObjectBehavior;

class ValidateAttributePropertiesImmutabilitySpec extends ObjectBehavior
{
    function let(FindConnectorAttributeByIdentifierAndCodeInterface $findConnectorAttribute)
    {
        $this->beConstructedWith($findConnectorAttribute);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValidateAttributePropertiesImmutability::class);
    }

    function it_does_not_return_any_error_when_no_immutable_property_is_changed(
        FindConnectorAttributeByIdentifierAndCodeInterface $findConnectorAttribute,
        ConnectorAttribute $attribute
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('description');
        $findConnectorAttribute->find($assetFamilyIdentifier, $attributeCode)->willReturn($attribute);
        $attribute->normalize()->willReturn([
            'code' => 'description',
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'labels' => [
                'en_US' => 'Description'
            ],
            'is_required_for_completeness' => false,
            'is_textarea' => false,
            'max_characters' => 12,
            'validation_rule' => 'regular_expression',
            'validation_regexp' => 'foo',
            'is_rich_text_editor' => true
        ]);

        $this->__invoke($assetFamilyIdentifier, $attributeCode, [
            'code' => 'description',
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
            'labels' => [
                'en_US' => 'Updated description'
            ],
            'is_required_for_completeness' => true,
            'is_textarea' => false,
            'max_characters' => null,
            'validation_rule' => 'none',
            'validation_regexp' => null,
            'is_rich_text_editor' => false
        ])->shouldReturn([]);
    }

    function it_returns_an_error_when_the_type_is_changed(
        FindConnectorAttributeByIdentifierAndCodeInterface $findConnectorAttribute,
        ConnectorAttribute $attribute
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('description');
        $findConnectorAttribute->find($assetFamilyIdentifier, $attributeCode)->willReturn($attribute);
        $attribute->normalize()->willReturn([
            'code' => 'description',
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
        ]);

        $errors = $this->__invoke($assetFamilyIdentifier, $attributeCode, [
            'code' => 'description',
            'type' => 'option',
        ]);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_the_value_per_channel_is_changed(
        FindConnectorAttributeByIdentifierAndCodeInterface $findConnectorAttribute,
        ConnectorAttribute $attribute
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('description');
        $findConnectorAttribute->find($assetFamilyIdentifier, $attributeCode)->willReturn($attribute);
        $attribute->normalize()->willReturn([
            'code' => 'description',
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
        ]);

        $errors = $this->__invoke($assetFamilyIdentifier, $attributeCode, [
            'code' => 'description',
            'type' => 'text',
            'value_per_channel' => false,
            'value_per_locale' => true,
        ]);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_the_value_per_locale_is_changed(
        FindConnectorAttributeByIdentifierAndCodeInterface $findConnectorAttribute,
        ConnectorAttribute $attribute
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('description');
        $findConnectorAttribute->find($assetFamilyIdentifier, $attributeCode)->willReturn($attribute);
        $attribute->normalize()->willReturn([
            'code' => 'description',
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => false,
        ]);

        $errors = $this->__invoke($assetFamilyIdentifier, $attributeCode, [
            'code' => 'description',
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
        ]);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_the_asset_family_code_is_changed(
        FindConnectorAttributeByIdentifierAndCodeInterface $findConnectorAttribute,
        ConnectorAttribute $attribute
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('description');
        $findConnectorAttribute->find($assetFamilyIdentifier, $attributeCode)->willReturn($attribute);
        $attribute->normalize()->willReturn([
            'code' => 'designer_country',
            'type' => 'asset',
            'value_per_channel' => true,
            'value_per_locale' => false,
            'asset_family_code' => 'country'
        ]);

        $errors = $this->__invoke($assetFamilyIdentifier, $attributeCode, [
            'code' => 'designer_country',
            'type' => 'asset',
            'value_per_channel' => true,
            'value_per_locale' => false,
            'asset_family_code' => null
        ]);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_all_the_errors_when_several_immutable_properties_are_changed(
        FindConnectorAttributeByIdentifierAndCodeInterface $findConnectorAttribute,
        ConnectorAttribute $attribute
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('description');
        $findConnectorAttribute->find($assetFamilyIdentifier, $attributeCode)->willReturn($attribute);
        $attribute->normalize()->willReturn([
            'code' => 'description',
            'type' => 'text',
            'value_per_channel' => true,
            'value_per_locale' => true,
        ]);

        $errors = $this->__invoke($assetFamilyIdentifier, $attributeCode, [
            'code' => 'description',
            'type' => 'text',
            'value_per_channel' => false,
            'value_per_locale' => false,
        ]);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }
}
