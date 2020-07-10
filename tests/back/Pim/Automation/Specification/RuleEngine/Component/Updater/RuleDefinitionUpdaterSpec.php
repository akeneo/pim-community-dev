<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Updater;

use Akeneo\Pim\Automation\RuleEngine\Component\Updater\RuleDefinitionUpdater;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionTranslation;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Doctrine\Common\Collections\ArrayCollection;
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

    function it_updates_a_rule_definition_from_a_rule(RuleDefinitionInterface $definition, RuleInterface $rule)
    {
        $rule->getCode()->willReturn('foo');
        $rule->getPriority()->willReturn(42);
        $rule->getType()->willReturn('product');
        $rule->getContent()->willReturn(['content of my rule']);

        $labelEnUs = new RuleDefinitionTranslation();
        $labelEnUs->setLocale('en_US');
        $labelEnUs->setLabel('Label en_US');
        $labelfrFr = new RuleDefinitionTranslation();
        $labelfrFr->setLocale('fr_FR');
        $labelfrFr->setLabel('Label fr_FR');
        $rule->getTranslations()->willReturn(new ArrayCollection([$labelEnUs, $labelfrFr]));

        $definition->setCode('foo')->shouldBeCalled();
        $definition->setPriority(42)->shouldBeCalled();
        $definition->setType('product')->shouldBeCalled();
        $definition->setContent(['content of my rule'])->shouldBeCalled();
        $definition->setLabel('en_US', 'Label en_US')->shouldBeCalled();
        $definition->setLabel('fr_FR', 'Label fr_FR')->shouldBeCalled();

        $this->fromRule($definition, $rule);
    }
}
