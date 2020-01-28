<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory\OptionAttributeFactory;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateMediaFileAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateOptionAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionAttributeFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(OptionAttributeFactory::class);
    }

    function it_only_supports_create_option_attribute_commands()
    {
        $this->supports(
            new CreateOptionAttributeCommand(
                'designer',
                'favorite_color',
                ['fr_FR' => 'Couleur favorite'],
                false,
                false,
                false,
                false
            )
        )->shouldReturn(true);
        $this->supports(
            new CreateMediaFileAttributeCommand(
                'designer',
                'name',
                [
                    'fr_FR' => 'Nom',
                ],
                true,
                false,
                false,
                false,
                null,
                [],
                MediaType::IMAGE
            )
        )->shouldReturn(false);
    }

    function it_creates_a_asset_attribute_with_a_command()
    {
        $command = new CreateOptionAttributeCommand(
            'designer',
            'favorite_color',
            ['fr_FR' => 'Couleur favorite'],
            false,
            false,
            false,
            false
        );

        $this->create(
            $command,
            AttributeIdentifier::fromString('favorite_color_designer_fingerprint'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier'                  => 'favorite_color_designer_fingerprint',
            'asset_family_identifier' => 'designer',
            'code'                        => 'favorite_color',
            'labels'                      => ['fr_FR' => 'Couleur favorite'],
            'order'                       => 0,
            'is_required'                 => false,
            'is_read_only'                => false,
            'value_per_channel'           => false,
            'value_per_locale'            => false,
            'type'                        => 'option',
            'options'                     => [],
        ]);
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
