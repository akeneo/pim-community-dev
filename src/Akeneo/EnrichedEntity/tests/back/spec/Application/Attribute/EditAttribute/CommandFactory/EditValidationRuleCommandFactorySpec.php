<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditRegularExpressionCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditRegularExpressionCommandFactory;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditValidationRuleCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditValidationRuleCommandFactory;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
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
            'identifier'          => ['identifier' => 'name', 'enriched_entity_identifier' => 'designer'],
            'validation_rule' => AttributeValidationRule::URL,
        ])->shouldReturn(true);
        $this->supports([
            'identifier' => ['identifier' => 'name', 'enriched_entity_identifier' => 'designer'],
            'labels'     => 'wrong_property',
        ])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_the_validation_rule()
    {
        $command = $this->create([
            'identifier' => [
                'identifier'                 => 'name',
                'enriched_entity_identifier' => 'designer',
            ],
            'validation_rule'   => AttributeValidationRule::EMAIL
        ]);
        $command->shouldBeAnInstanceOf(EditValidationRuleCommand::class);
        $command->identifier->shouldBeEqualTo([
            'identifier'                 => 'name',
            'enriched_entity_identifier' => 'designer',
        ]);
        $command->validationRule->shouldBeEqualTo(AttributeValidationRule::EMAIL);
    }

    function it_throws_if_it_cannot_create_the_command()
    {
        $this->shouldThrow(\RuntimeException::class)
            ->during('create', [
                [
                    'identifier'     => [
                        'identifier'                 => 'portrait',
                        'enriched_entity_identifier' => 'designer',
                    ],
                    'wrong_property' => 10,
                ],
            ]);
    }
}
