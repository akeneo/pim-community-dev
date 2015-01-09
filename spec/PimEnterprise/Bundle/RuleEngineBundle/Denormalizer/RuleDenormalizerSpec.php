<?php

namespace spec\PimEnterprise\Bundle\RuleEngineBundle\Denormalizer;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\ConditionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class RuleDenormalizerSpec extends ObjectBehavior
{
    public function let(DenormalizerInterface $contentDernomalizer) {
        $this->beConstructedWith(
            $contentDernomalizer,
            'PimEnterprise\Bundle\RuleEngineBundle\Model\Rule',
            'PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinition',
            'foo'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Denormalizer\RuleDenormalizer');
    }

    function it_denormalizes_a_rule($contentDernomalizer)
    {
        $contentDernomalizer->denormalize(Argument::cetera())->willReturn(['conditions' => [], 'actions' => []]);

        $this->denormalize(['code' => 'discharge_fr_description', 'conditions' => [], 'actions' => []], Argument::any())
            ->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Model\Rule');
    }

    function it_denormalizes_a_rule_provided_in_the_context(
        $contentDernomalizer,
        RuleInterface $rule,
        ConditionInterface $condition,
        ProductSetValueActionInterface $setValueAction,
        ProductCopyValueActionInterface $copyValueAction

    ) {
        $rule->getContent()->willReturn([]);
        $rule->setCode('discharge_fr_description')->shouldBeCalled();
        $rule->setType('foo')->shouldBeCalled();
        $rule->setPriority(10)->shouldBeCalled();
        $rule->addCondition($condition)->shouldBeCalled();
        $rule->addAction($setValueAction)->shouldBeCalled();
        $rule->addAction($copyValueAction)->shouldBeCalled();

        $contentDernomalizer->denormalize(Argument::cetera())->willReturn(['conditions' => [$condition], 'actions' => [$setValueAction, $copyValueAction]]);

        // TODO: really spec it...
        $this->denormalize(
            ['code' => 'discharge_fr_description', 'priority' => 10, 'conditions' => [], 'actions' => []],
            Argument::any(),
            Argument::any(),
            ['object' => $rule]
        );
    }

    function it_supports_denormalization()
    {
        $type = 'PimEnterprise\Bundle\RuleEngineBundle\Model\Rule';

        $this->supportsDenormalization(Argument::any(), $type)->shouldReturn(true);
    }

    function it_does_not_support_denormalization_for_invalid_data()
    {
        $type = 'PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCondition';

        $this->supportsDenormalization(Argument::any(), $type)->shouldReturn(false);
    }
}
