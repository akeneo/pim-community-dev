<?php

namespace spec\Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory\TextAttributeFactory;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateMediaFileAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use PhpSpec\ObjectBehavior;

class TextAttributeFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TextAttributeFactory::class);
    }

    function it_only_supports_create_text_commands()
    {
        $this->supports(
            new CreateTextAttributeCommand(
                'designer',
                'name',
                ['fr_FR' => 'Nom'],
                true,
                false,
                false,
                155,
                false,
                false,
                AttributeValidationRule::NONE,
                AttributeRegularExpression::EMPTY
            )
        )->shouldReturn(true);
        $this->supports(
            new CreateMediaFileAttributeCommand(
                'designer',
                'image',
                [
                    'fr_FR' => 'Image',
                ],
                true,
                false,
                false,
                null,
                [],
                MediaType::IMAGE
            )
        )->shouldReturn(false);
    }

    function it_creates_a_simple_text_attribute_with_a_command()
    {
        $command = new CreateTextAttributeCommand(
            'designer',
            'name',
            ['fr_FR' => 'Nom'],
            true,
            false,
            false,
            155,
            false,
            false,
            AttributeValidationRule::NONE,
            AttributeRegularExpression::EMPTY
        );

        $this->create(
            $command,
            AttributeIdentifier::fromString('name_designer_test'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn(
            [
                'identifier'                  => 'name_designer_test',
                'asset_family_identifier' => 'designer',
                'code'                        => 'name',
                'labels'                      => ['fr_FR' => 'Nom'],
                'order'                       => 0,
                'is_required'                 => true,
                'value_per_channel'           => false,
                'value_per_locale'            => false,
                'type'                        => 'text',
                'max_length'                  => 155,
                'is_textarea'                 => false,
                'is_rich_text_editor'         => false,
                'validation_rule'             => 'none',
                'regular_expression'          => null,
            ]
        );
    }

    function it_creates_a_simple_text_attribute_having_no_validation_with_a_command()
    {
        $command = new CreateTextAttributeCommand(
            'designer',
            'name',
            ['fr_FR' => 'Nom'],
            true,
            false,
            false,
            155,
            false,
            false,
            AttributeValidationRule::NONE,
            AttributeRegularExpression::EMPTY
        );

        $this->create(
            $command,
            AttributeIdentifier::fromString('name_designer_test'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn(
            [
                'identifier'                  => 'name_designer_test',
                'asset_family_identifier' => 'designer',
                'code'                        => 'name',
                'labels'                      => ['fr_FR' => 'Nom'],
                'order'                       => 0,
                'is_required'                 => true,
                'value_per_channel'           => false,
                'value_per_locale'            => false,
                'type'                        => 'text',
                'max_length'                  => 155,
                'is_textarea'                 => false,
                'is_rich_text_editor'         => false,
                'validation_rule'             => 'none',
                'regular_expression'          => null,
            ]
        );
    }

    function it_creates_a_simple_text_attribute_with_no_max_length_limit()
    {
        $command = new CreateTextAttributeCommand(
            'designer',
            'name',
            ['fr_FR' => 'Nom'],
            true,
            false,
            false,
            AttributeMaxLength::NO_LIMIT,
            false,
            false,
            AttributeValidationRule::NONE,
            AttributeRegularExpression::EMPTY
        );

        $this->create(
            $command,
            AttributeIdentifier::fromString('name_designer_test'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn(
            [
                'identifier'                  => 'name_designer_test',
                'asset_family_identifier' => 'designer',
                'code'                        => 'name',
                'labels'                      => ['fr_FR' => 'Nom'],
                'order'                       => 0,
                'is_required'                 => true,
                'value_per_channel'           => false,
                'value_per_locale'            => false,
                'type'                        => 'text',
                'max_length'                  => null,
                'is_textarea'                 => false,
                'is_rich_text_editor'         => false,
                'validation_rule'             => 'none',
                'regular_expression'          => null,
            ]
        );
    }

    function it_creates_a_simple_text_attribute_with_a_regular_expression_validation()
    {
        $command = new CreateTextAttributeCommand(
            'designer',
            'name',
            ['fr_FR' => 'Nom'],
            true,
            false,
            false,
            AttributeMaxLength::NO_LIMIT,
            false,
            false,
            AttributeValidationRule::REGULAR_EXPRESSION,
            '/\w+/'
        );

        $this->create(
            $command,
            AttributeIdentifier::fromString('name_designer_test'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier'                  => 'name_designer_test',
            'asset_family_identifier' => 'designer',
            'code'                        => 'name',
            'labels'                      => ['fr_FR' => 'Nom'],
            'order'                       => 0,
            'is_required'                 => true,
            'value_per_channel'           => false,
            'value_per_locale'            => false,
            'type'                        => 'text',
            'max_length'                  => null,
            'is_textarea'                 => false,
            'is_rich_text_editor'         => false,
            'validation_rule'             => 'regular_expression',
            'regular_expression'          => '/\w+/',
        ]);
    }

    function it_creates_a_textarea_with_rich_editor()
    {
        $command = new CreateTextAttributeCommand(
            'designer',
            'name',
            ['fr_FR' => 'Nom'],
            true,
            false,
            false,
            155,
            true,
            true,
            AttributeValidationRule::NONE,
            AttributeRegularExpression::EMPTY
        );
        $command->assetFamilyIdentifier = 'designer';
        $command->code = 'name';
        $command->labels = ['fr_FR' => 'Nom'];
        $command->isRequired = true;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->maxLength = 155;
        $command->isTextarea = true;
        $command->isRichTextEditor = true;

        $this->create(
            $command,
            AttributeIdentifier::fromString('name_designer_test'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier'                  => 'name_designer_test',
            'asset_family_identifier' => 'designer',
            'code'                        => 'name',
            'labels'                      => ['fr_FR' => 'Nom'],
            'order'                       => 0,
            'is_required'                 => true,
            'value_per_channel'           => false,
            'value_per_locale'            => false,
            'type'                        => 'text',
            'max_length'                  => 155,
            'is_textarea'                 => true,
            'is_rich_text_editor'         => true,
            'validation_rule'             => 'none',
            'regular_expression'          => null,
        ]);
    }
}
