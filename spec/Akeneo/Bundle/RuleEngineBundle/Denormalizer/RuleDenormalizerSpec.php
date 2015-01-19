<?php

namespace spec\Akeneo\Bundle\RuleEngineBundle\Denormalizer;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\ConditionInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class RuleDenormalizerSpec extends ObjectBehavior
{
    function let(DenormalizerInterface $chainedDernomalizer)
    {
        $this->beConstructedWith(
            'Akeneo\Bundle\RuleEngineBundle\Model\Rule',
            'Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinition',
            'foo'
        );

        $this->setChainedDenormalizer($chainedDernomalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\RuleEngineBundle\Denormalizer\RuleDenormalizer');
    }

    function it_denormalizes_a_rule($chainedDernomalizer)
    {
        $chainedDernomalizer->denormalize(Argument::cetera())->willReturn(['conditions' => [], 'actions' => []]);

        $this->denormalize(['code' => 'discharge_fr_description', 'conditions' => [], 'actions' => []], Argument::any())
            ->shouldHaveType('Akeneo\Bundle\RuleEngineBundle\Model\Rule');
    }

    function it_denormalizes_a_rule_provided_in_the_context(
        $chainedDernomalizer,
        RuleInterface $rule,
        ConditionInterface $condition,
        ProductSetValueActionInterface $setValueAction,
        ProductCopyValueActionInterface $copyValueAction

    ) {
        $rawContent = ['conditions' => ['my conditions are here'], 'actions' => ['my actions are there']];

        $rule->getContent()->willReturn([]);
        $rule->setCode('discharge_fr_description')->shouldBeCalled();
        $rule->setType('foo')->shouldBeCalled();
        $rule->setPriority(10)->shouldBeCalled();
        $rule->setContent($rawContent)->shouldBeCalled();
        $rule->addCondition($condition)->shouldBeCalled();
        $rule->addAction($setValueAction)->shouldBeCalled();
        $rule->addAction($copyValueAction)->shouldBeCalled();

        $chainedDernomalizer->denormalize($rawContent, Argument::cetera())->willReturn(['conditions' => [$condition], 'actions' => [$setValueAction, $copyValueAction]]);

        // TODO: really spec it...
        $this->denormalize(
            ['code' => 'discharge_fr_description', 'priority' => 10] + $rawContent,
            Argument::any(),
            Argument::any(),
            ['object' => $rule]
        );
    }

    function it_supports_denormalization()
    {
        $type = 'Akeneo\Bundle\RuleEngineBundle\Model\Rule';

        $this->supportsDenormalization(Argument::any(), $type)->shouldReturn(true);
    }

    function it_does_not_support_denormalization_for_invalid_data()
    {
        $type = 'PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCondition';

        $this->supportsDenormalization(Argument::any(), $type)->shouldReturn(false);
    }
}
