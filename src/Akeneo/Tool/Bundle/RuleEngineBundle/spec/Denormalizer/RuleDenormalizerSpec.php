<?php

namespace spec\Akeneo\Tool\Bundle\RuleEngineBundle\Denormalizer;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ConditionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCopyActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSetActionInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class RuleDenormalizerSpec extends ObjectBehavior
{
    function let(DenormalizerInterface $chainedDernomalizer)
    {
        $this->beConstructedWith(
            'Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule',
            'Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition',
            'foo'
        );

        $this->setChainedDenormalizer($chainedDernomalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Tool\Bundle\RuleEngineBundle\Denormalizer\RuleDenormalizer');
    }

    function it_denormalizes_a_rule($chainedDernomalizer)
    {
        $chainedDernomalizer->denormalize(Argument::cetera())->willReturn(['conditions' => [], 'actions' => []]);

        $this->denormalize(['code' => 'discharge_fr_description', 'conditions' => [], 'actions' => []], Argument::any())
            ->shouldHaveType('Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule');
    }

    function it_denormalizes_a_rule_provided_in_the_context(
        $chainedDernomalizer,
        RuleInterface $rule,
        ConditionInterface $condition,
        ProductSetActionInterface $setAction,
        ProductCopyActionInterface $copyAction

    ) {
        $rawContent = ['conditions' => ['my conditions are here'], 'actions' => ['my actions are there']];

        $rule->getContent()->willReturn([]);
        $rule->setCode('discharge_fr_description')->shouldBeCalled();
        $rule->setType('foo')->shouldBeCalled();
        $rule->setPriority(10)->shouldBeCalled();
        $rule->setContent($rawContent)->shouldBeCalled();
        $rule->addCondition($condition)->shouldBeCalled();
        $rule->addAction($setAction)->shouldBeCalled();
        $rule->addAction($copyAction)->shouldBeCalled();
        $rule->setEnabled(false)->shouldBeCalled();

        $chainedDernomalizer->denormalize($rawContent, Argument::cetera())
            ->willReturn(['conditions' => [$condition], 'actions' => [$setAction, $copyAction]]);

        // TODO: really spec it...
        $this->denormalize(
            ['code' => 'discharge_fr_description', 'priority' => 10, 'enabled' => false] + $rawContent,
            Argument::any(),
            Argument::any(),
            ['object' => $rule]
        );
    }

    function it_supports_denormalization()
    {
        $type = 'Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule';

        $this->supportsDenormalization(Argument::any(), $type)->shouldReturn(true);
    }

    function it_does_not_support_denormalization_for_invalid_data()
    {
        $type = 'Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCondition';

        $this->supportsDenormalization(Argument::any(), $type)->shouldReturn(false);
    }
}
