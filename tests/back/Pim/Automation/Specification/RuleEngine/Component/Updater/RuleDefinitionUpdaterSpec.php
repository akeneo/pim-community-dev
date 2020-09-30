<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Updater;

use Akeneo\Pim\Automation\RuleEngine\Component\Updater\RuleDefinitionUpdater;
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

    function it_updates_a_rule_definition_from_a_rule(RuleDefinitionInterface $definition, RuleInterface $rule)
    {
        $rule->getCode()->willReturn('foo');
        $rule->getPriority()->willReturn(42);
        $rule->getType()->willReturn('product');
        $rule->getContent()->willReturn(['content of my rule']);
        $rule->isEnabled()->willReturn(false);

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
        $definition->setEnabled(false)->shouldBeCalled();

        $this->fromRule($definition, $rule);
    }
}
