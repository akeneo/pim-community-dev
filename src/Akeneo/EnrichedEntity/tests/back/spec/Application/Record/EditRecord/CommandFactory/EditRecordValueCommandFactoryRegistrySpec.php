<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditRecordValueCommandFactoryRegistry;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditTextValueCommandFactory;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\TextEditRecordCommandFactory;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class EditRecordValueCommandFactoryRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditRecordValueCommandFactoryRegistry::class);
    }

    function it_registers_a_record_value_command_factory_and_return_it_if_it_supports(
        EditTextValueCommandFactory $editTextValueCommandFactory,
        TextAttribute $name
    ) {
        $editTextValueCommandFactory->supports($name)->willReturn(true);
        $this->register($editTextValueCommandFactory);
        $this->getFactory($name)->shouldReturn($editTextValueCommandFactory);
    }

    function it_throws_if_it_does_not_find_a_value_command_factory_that_supports(
        EditTextValueCommandFactory $editTextValueCommandFactory,
        AttributeIdentifier $attributeIdentifier,
        TextAttribute $name
    ) {
        $editTextValueCommandFactory->supports($name)->willReturn(false);
        $attributeIdentifier->normalize()->willReturn('designer_name_fingerprint');
        $name->getIdentifier()->willReturn($attributeIdentifier);
        $this->register($editTextValueCommandFactory);
        $this->shouldThrow(\RuntimeException::class)->during('getFactory', [$name]);
    }
}
