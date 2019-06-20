<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory\OptionCollectionAttributeFactory;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateOptionCollectionAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionCollectionAttributeFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(OptionCollectionAttributeFactory::class);
    }

    function it_only_supports_create_option_attribute_commands()
    {
        $this->supports(
            new CreateOptionCollectionAttributeCommand(
                'designer',
                'favorite_color',
                ['fr_FR' => 'Couleur favorite'],
                false,
                false,
                false
            )
        )->shouldReturn(true);
        $this->supports(
            new CreateImageAttributeCommand(
                'designer',
                'name',
                [
                    'fr_FR' => 'Nom',
                ],
                true,
                false,
                false,
                null,
                []
            )
        )->shouldReturn(false);
    }

    function it_creates_a_record_attribute_with_a_command()
    {
        $command = new CreateOptionCollectionAttributeCommand(
            'designer',
            'favorites_color',
            ['fr_FR' => 'Couleur favorites'],
            false,
            false,
            false
        );

        $this->create(
            $command,
            AttributeIdentifier::fromString('favorites_color_designer_fingerprint'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier'                  => 'favorites_color_designer_fingerprint',
            'reference_entity_identifier' => 'designer',
            'code'                        => 'favorites_color',
            'labels'                      => ['fr_FR' => 'Couleur favorites'],
            'order'                       => 0,
            'is_required'                 => false,
            'value_per_channel'           => false,
            'value_per_locale'            => false,
            'type'                        => 'option_collection',
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
