<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator\TextAttributeHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use PhpSpec\ObjectBehavior;

class TextAttributeHydratorSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $connection->getDatabasePlatform()->willReturn(new MySQLPlatform());
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TextAttributeHydrator::class);
    }

    function it_only_supports_the_hydration_of_text_attributes()
    {
        $this->supports(['attribute_type' => 'text'])->shouldReturn(true);
        $this->supports(['attribute_type' => 'image'])->shouldReturn(false);
        $this->supports([])->shouldReturn(false);
    }

    function it_throws_if_any_of_the_required_keys_are_not_present_to_hydrate()
    {
        $this->shouldThrow(\RuntimeException::class)->during('hydrate', [['wrong_key' => 'wrong_value']]);
    }

    function it_hydrates_a_text_area_with_a_max_length()
    {
        $textArea = $this->hydrate([
            'identifier'                 => 'description_designer_fingerprint',
            'code'                       => 'description',
            'asset_family_identifier' => 'designer',
            'labels'                     => json_encode(['fr_FR' => 'Bio']),
            'attribute_type'             => 'text',
            'attribute_order'            => '0',
            'is_required'                => '1',
            'is_read_only'               => '1',
            'value_per_channel'          => '0',
            'value_per_locale'           => '1',
            'wrong_key'                  => '1',
            'additional_properties'      => json_encode([
                'max_length'          => '255',
                'is_textarea'         => true,
                'is_rich_text_editor' => true,
                'validation_rule'     => null,
                'regular_expression'  => null,
            ]),
        ]);
        $textArea->shouldBeAnInstanceOf(TextAttribute::class);
        $textArea->normalize()->shouldBe([
            'identifier'                 => 'description_designer_fingerprint',
            'asset_family_identifier' => 'designer',
            'code'                       => 'description',
            'labels'                     => ['fr_FR' => 'Bio'],
            'order'                      => 0,
            'is_required'                => true,
            'is_read_only'               => true,
            'value_per_channel'          => false,
            'value_per_locale'           => true,
            'type'                       => 'text',
            'max_length'                 => 255,
            'is_textarea'                => true,
            'is_rich_text_editor'        => true,
            'validation_rule'            => 'none',
            'regular_expression'         => null,
        ]);
    }

    function it_hydrates_a_simple_text_with_no_max_length_and_no_validation_rule()
    {
        $textArea = $this->hydrate([
            'identifier'                 => 'name_designer_fingerprint',
            'code'                       => 'name',
            'asset_family_identifier' => 'designer',
            'labels'                     => json_encode(['fr_FR' => 'Nom']),
            'attribute_type'             => 'text',
            'attribute_order'            => '0',
            'is_required'                => '0',
            'is_read_only'               => '1',
            'value_per_channel'          => '1',
            'value_per_locale'           => '0',
            'additional_properties'      => json_encode([
                'max_length'          => null,
                'is_textarea'         => false,
                'is_rich_text_editor' => false,
                'validation_rule'     => null,
                'regular_expression'  => null,
            ]),
        ]);
        $textArea->shouldBeAnInstanceOf(TextAttribute::class);
        $textArea->normalize()->shouldBe([
            'identifier'                 => 'name_designer_fingerprint',
            'asset_family_identifier' => 'designer',
            'code'                       => 'name',
            'labels'                     => ['fr_FR' => 'Nom'],
            'order'                      => 0,
            'is_required'                => false,
            'is_read_only'               => true,
            'value_per_channel'          => true,
            'value_per_locale'           => false,
            'type'                       => 'text',
            'max_length'                 => null,
            'is_textarea'                => false,
            'is_rich_text_editor'        => false,
            'validation_rule'            => 'none',
            'regular_expression'         => null,
        ]);
    }

    function it_hydrates_a_text_attribute_which_is_an_email()
    {
        $textArea = $this->hydrate([
            'identifier'                 => 'email_address_designer_fingerprint',
            'code'                       => 'email_address',
            'asset_family_identifier' => 'designer',
            'labels'                     => json_encode(['fr_FR' => 'Email']),
            'attribute_type'             => 'text',
            'attribute_order'            => '0',
            'is_required'                => '0',
            'is_read_only'               => '0',
            'value_per_channel'          => '1',
            'value_per_locale'           => '0',
            'additional_properties'      => json_encode([
                'max_length'          => null,
                'is_textarea'         => false,
                'is_rich_text_editor' => false,
                'validation_rule'     => 'email',
                'regular_expression'  => null,
            ]),
        ]);
        $textArea->shouldBeAnInstanceOf(TextAttribute::class);
        $textArea->normalize()->shouldBe([
            'identifier'                 => 'email_address_designer_fingerprint',
            'asset_family_identifier' => 'designer',
            'code'                       => 'email_address',
            'labels'                     => ['fr_FR' => 'Email'],
            'order'                      => 0,
            'is_required'                => false,
            'is_read_only'               => false,
            'value_per_channel'          => true,
            'value_per_locale'           => false,
            'type'                       => 'text',
            'max_length'                 => null,
            'is_textarea'                => false,
            'is_rich_text_editor'        => false,
            'validation_rule'            => 'email',
            'regular_expression'         => null,
        ]);
    }

    function it_hydrates_a_simple_text_with_a_validation_rule_email()
    {
        $textArea = $this->hydrate([
            'identifier'                 => 'name_designer_fingerprint',
            'code'                       => 'name',
            'asset_family_identifier' => 'designer',
            'labels'                     => json_encode(['fr_FR' => 'Nom']),
            'attribute_type'             => 'text',
            'attribute_order'            => '0',
            'is_required'                => '0',
            'is_read_only'               => '0',
            'value_per_channel'          => '1',
            'value_per_locale'           => '0',
            'additional_properties'      => json_encode([
                'max_length'          => null,
                'is_textarea'         => false,
                'is_rich_text_editor' => false,
                'validation_rule'     => 'email',
                'regular_expression'  => null,
            ]),
        ]);
        $textArea->shouldBeAnInstanceOf(TextAttribute::class);
        $textArea->normalize()->shouldBe([
            'identifier'                 => 'name_designer_fingerprint',
            'asset_family_identifier' => 'designer',
            'code'                       => 'name',
            'labels'                     => ['fr_FR' => 'Nom'],
            'order'                      => 0,
            'is_required'                => false,
            'is_read_only'               => false,
            'value_per_channel'          => true,
            'value_per_locale'           => false,
            'type'                       => 'text',
            'max_length'                 => null,
            'is_textarea'                => false,
            'is_rich_text_editor'        => false,
            'validation_rule'            => 'email',
            'regular_expression'         => null,
        ]);
    }

    function it_hydrates_a_simple_text_with_having_a_regular_expression()
    {
        $textArea = $this->hydrate([
            'identifier'                 => 'regular_expression_designer_fingerprint',
            'code'                       => 'regular_expression',
            'asset_family_identifier' => 'designer',
            'labels'                     => json_encode([]),
            'attribute_type'             => 'text',
            'attribute_order'            => '0',
            'is_required'                => '0',
            'is_read_only'               => '0',
            'value_per_channel'          => '1',
            'value_per_locale'           => '0',
            'additional_properties'      => json_encode([
                'max_length'          => null,
                'is_textarea'         => false,
                'is_rich_text_editor' => false,
                'validation_rule'     => 'regular_expression',
                'regular_expression'  => '/[0-9]+a*/',
            ]),
        ]);
        $textArea->shouldBeAnInstanceOf(TextAttribute::class);
        $textArea->normalize()->shouldBe([
            'identifier'                 => 'regular_expression_designer_fingerprint',
            'asset_family_identifier' => 'designer',
            'code'                       => 'regular_expression',
            'labels'                     => [],
            'order'                      => 0,
            'is_required'                => false,
            'is_read_only'               => false,
            'value_per_channel'          => true,
            'value_per_locale'           => false,
            'type'                       => 'text',
            'max_length'                 => null,
            'is_textarea'                => false,
            'is_rich_text_editor'        => false,
            'validation_rule'            => 'regular_expression',
            'regular_expression'         => '/[0-9]+a*/',
        ]);
    }
}
