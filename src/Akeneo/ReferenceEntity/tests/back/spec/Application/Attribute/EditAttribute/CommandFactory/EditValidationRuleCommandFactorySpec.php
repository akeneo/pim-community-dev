<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditValidationRuleCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditValidationRuleCommandFactory;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use PhpSpec\ObjectBehavior;

class EditValidationRuleCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditValidationRuleCommandFactory::class);
    }

    function it_only_supports_validation_rule_edits()
    {
        $this->supports([
            'identifier'          => 'name',
            'validation_rule' => AttributeValidationRule::URL,
        ])->shouldReturn(true);
        $this->supports([
            'identifier'      => 'name',
            'validation_rule' => null,
        ])->shouldReturn(true);
        $this->supports([
            'identifier' => 'name',
            'labels'     => 'wrong_property',
        ])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_the_validation_rule()
    {
        $command = $this->create([
            'identifier' => 'name',
            'validation_rule'   => AttributeValidationRule::EMAIL
        ]);
        $command->shouldBeAnInstanceOf(EditValidationRuleCommand::class);
        $command->identifier->shouldBeEqualTo('name');
        $command->validationRule->shouldBeEqualTo(AttributeValidationRule::EMAIL);
    }

    function it_throws_if_it_cannot_create_the_command()
    {
        $this->shouldThrow(\RuntimeException::class)
            ->during('create', [
                [
                    'identifier'     => 'portrait',
                    'wrong_property' => 10,
                ],
            ]);
    }
}
