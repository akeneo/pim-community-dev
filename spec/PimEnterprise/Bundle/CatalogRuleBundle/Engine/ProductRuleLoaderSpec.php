<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Engine;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductRuleLoaderSpec extends ObjectBehavior
{
    public function let(EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith(
            $eventDispatcher,
            'PimEnterprise\Bundle\RuleEngineBundle\Model\Rule',
            'PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueAction',
            'PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueAction'
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleLoader');
    }

    public function it_is_a_rule_loader()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Engine\LoaderInterface');
    }

    public function it_supports_a_product_rule(
        RuleDefinitionInterface $definitionOk,
        RuleDefinitionInterface $definitionKo
    ) {
        $definitionOk->getType()->willReturn('product');
        $definitionKo->getType()->willReturn('foo');

        $this->supports($definitionOk)->shouldReturn(true);
        $this->supports($definitionKo)->shouldReturn(false);
    }

    public function it_loads_a_rule($eventDispatcher, RuleDefinitionInterface $definition)
    {
        $content =
            <<<CONTENT
            {"conditions":[{"field":"weight","operator":">","value":"123"},{"field":"sku","operator":"STARTS WITH","value":"sku-4372"}],"actions":[{"type":"set_value","field":"name","value":"toto"},{"type":"copy_value","from_field":"description","to_field":"description","from_scope":"tablet","from_locale":"fr_FR","to_scope":"mobile","to_locale":"fr_CH"}]}
CONTENT;

        $definition->getContent()->willReturn($content);

        $eventDispatcher->dispatch(RuleEvents::PRE_LOAD, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(RuleEvents::POST_LOAD, Argument::any())->shouldBeCalled();

        $this->load($definition)->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface');
    }

    public function it_does_not_load_a_rule_with_bad_content(
        $eventDispatcher,
        RuleDefinitionInterface $definition1,
        RuleDefinitionInterface $definition2
    ) {
        $definition1->getContent()->willReturn(json_encode(['actions' => []]));
        $definition2->getContent()->willReturn(json_encode(['conditions' => []]));
        $definition1->getCode()->willReturn('rule1');
        $definition2->getCode()->willReturn('rule2');

        $eventDispatcher->dispatch(RuleEvents::PRE_LOAD, Argument::any())->shouldBeCalled();

        $this
            ->shouldThrow(new \LogicException('Rule "rule1" should have a "conditions" key in its content.'))
            ->during('load', [$definition1])
        ;
        $this
            ->shouldThrow(new \LogicException('Rule "rule2" should have a "actions" key in its content.'))
            ->during('load', [$definition2])
        ;
    }
}
