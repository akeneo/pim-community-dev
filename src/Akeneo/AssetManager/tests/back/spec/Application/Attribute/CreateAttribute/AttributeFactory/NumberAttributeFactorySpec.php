<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory\NumberAttributeFactory;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateNumberAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateOptionAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class NumberAttributeFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NumberAttributeFactory::class);
    }

    function it_only_supports_create_number_attribute_commands()
    {
        $createNumberAttribute = new CreateNumberAttributeCommand(
            'designer',
            'number',
            ['fr_FR' => 'Couleur favorite'],
            false,
            false,
            false,
            false,
            '150',
            '200'
        );
        $unsupportedCommand = new CreateImageAttributeCommand(
            'designer',
            'image',
            [
                'fr_FR' => 'Nom',
            ],
            true,
            false,
            false,
            null,
            []
        );

        $this->supports($createNumberAttribute)->shouldReturn(true);
        $this->supports($unsupportedCommand)->shouldReturn(false);
    }

    function it_creates_a_number_attribute_with_a_command_having_a_min_and_max_value()
    {
        $command = new CreateNumberAttributeCommand(
            'designer',
            'number',
            ['fr_FR' => 'Nombre'],
            false,
            false,
            false,
            false,
            '150',
            '200'
        );

        $this->create(
            $command,
            AttributeIdentifier::fromString('favorite_color_designer_fingerprint'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn(
            [
                'identifier'                  => 'favorite_color_designer_fingerprint',
                'asset_family_identifier' => 'designer',
                'code'                        => 'number',
                'labels'                      => ['fr_FR' => 'Nombre'],
                'order'                       => 0,
                'is_required'                 => false,
                'value_per_channel'           => false,
                'value_per_locale'            => false,
                'type'                        => 'number',
                'decimals_allowed'            => false,
                'min_value'                   => '150',
                'max_value'                   => '200',
            ]
        );
    }

    function it_creates_a_number_attribute_with_a_command_without_min_and_max_value()
    {
        $command = new CreateNumberAttributeCommand(
            'designer',
            'number',
            ['fr_FR' => 'Nombre'],
            false,
            false,
            false,
            false,
            null,
            null
        );

        $this->create(
            $command,
            AttributeIdentifier::fromString('favorite_color_designer_fingerprint'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn(
            [
                'identifier'                  => 'favorite_color_designer_fingerprint',
                'asset_family_identifier' => 'designer',
                'code'                        => 'number',
                'labels'                      => ['fr_FR' => 'Nombre'],
                'order'                       => 0,
                'is_required'                 => false,
                'value_per_channel'           => false,
                'value_per_locale'            => false,
                'type'                        => 'number',
                'decimals_allowed'            => false,
                'min_value'                   => null,
                'max_value'                   => null,
            ]
        );
    }

    public function it_throws_if_it_cannot_create_the_attribute_from_an_unsupported_command()
    {
        $this->shouldThrow(\RuntimeException::class)
             ->during(
                 'create', [
                     new CreateTextAttributeCommand(
                         'designer',
                         'color',
                         [],
                         false,
                         false,
                         false,
                         null,
                         false,
                         false,
                         null,
                         null
                     ),
                     AttributeIdentifier::fromString('unsupported_attribute'),
                     AttributeOrder::fromInteger(0),
                 ]
             );
    }
}
