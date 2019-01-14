<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory\OptionAttributeFactory;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateOptionAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
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
        $this->supports(new CreateOptionAttributeCommand())->shouldReturn(true);
        $this->supports(new CreateImageAttributeCommand())->shouldReturn(false);
    }

    function it_creates_a_record_attribute_with_a_command()
    {
        $command = new CreateOptionAttributeCommand();
        $command->referenceEntityIdentifier = 'designer';
        $command->code = 'favorite_color';
        $command->labels = ['fr_FR' => 'Couleur favorite'];
        $command->order = 0;
        $command->isRequired = false;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;

        $this->create(
            $command,
            AttributeIdentifier::fromString('favorite_color_designer_fingerprint'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier' => 'favorite_color_designer_fingerprint',
            'reference_entity_identifier' => 'designer',
            'code' => 'favorite_color',
            'labels' => ['fr_FR' => 'Couleur favorite'],
            'order' => 0,
            'is_required' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'type' => 'option',
            'options' => []
        ]);
    }

    public function it_throws_if_it_cannot_create_the_attribute_from_an_unsupported_command()
    {
        $this->shouldThrow(\RuntimeException::class)
            ->during(
                'create', [
                    new CreateTextAttributeCommand(),
                    AttributeIdentifier::fromString('unsupported_attribute'),
                    AttributeOrder::fromInteger(0)
                ]
            );
    }
}
