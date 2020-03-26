<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Updater;

use Akeneo\Pim\Automation\RuleEngine\Component\Updater\RuleDefinitionUpdater;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use PhpSpec\ObjectBehavior;

class RuleDefinitionUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RuleDefinitionUpdater::class);
    }

    function it_updates_a_rule_definition(
        RuleDefinitionInterface $ruleDefinition
    ) {
        $ruleDefinition->setCode('123')->shouldBeCalled();
        $ruleDefinition->setType('add')->shouldBeCalled();
        $ruleDefinition->setPriority(10)->shouldBeCalled();
        $ruleDefinition->setContent(['actions' => ['action'], 'conditions' => []])->shouldBeCalled();

        $this->update($ruleDefinition, [
            'code' => '123',
            'type' => 'add',
            'priority' => 10,
            'content' => ['actions' => ['action'], 'conditions' => []],
        ]);
    }

    function it_throws_an_error_if_key_does_not_exist(
        RuleDefinitionInterface $ruleDefinition
    ) {
        $this->shouldThrow(\InvalidArgumentException::class)->during('update', [$ruleDefinition, ['foo' => 'bar']]);
    }
}
