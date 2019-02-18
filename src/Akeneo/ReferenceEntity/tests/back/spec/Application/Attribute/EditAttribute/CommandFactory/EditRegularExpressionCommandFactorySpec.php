<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditRegularExpressionCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditRegularExpressionCommandFactory;
use PhpSpec\ObjectBehavior;

class EditRegularExpressionCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditRegularExpressionCommandFactory::class);
    }

    function it_only_supports_attribute_property_regular_expression_edits()
    {
        $this->supports([
            'identifier'         => 'name',
            'regular_expression' => '/\w+/',
        ])->shouldReturn(true);
        $this->supports([
            'identifier'         => 'name',
            'regular_expression' => null,
        ])->shouldReturn(true);
        $this->supports([
            'identifier' => 'name',
            'labels'     => 'wrong_property',
        ])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_the_regular_expression()
    {
        $command = $this->create([
            'identifier' => 'name',
            'regular_expression'   => '/\w+/',
        ]);
        $command->shouldBeAnInstanceOf(EditRegularExpressionCommand::class);
        $command->identifier->shouldBeEqualTo('name');
        $command->regularExpression->shouldBeEqualTo('/\w+/');
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
