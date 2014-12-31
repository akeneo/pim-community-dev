<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Serializer;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductCopyValueActionNormalizer;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleConditionNormalizer;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductSetValueActionNormalizer;
use Prophecy\Argument;

class ProductRuleDenormalizerSpec extends ObjectBehavior
{
    public function let(
        ProductRuleConditionNormalizer $conditionNormalizer,
        ProductSetValueActionNormalizer $setValueActionNormalizer,
        ProductCopyValueActionNormalizer $copyValueActionNormalizer
    ) {
        $this->beConstructedWith(
            $conditionNormalizer,
            $setValueActionNormalizer,
            $copyValueActionNormalizer,
            'PimEnterprise\Bundle\RuleEngineBundle\Model\Rule',
            'PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinition'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleDenormalizer');
    }

    function it_denormalizes()
    {
        // TODO: really spec it...
        $this->denormalize(['code' => 'discharge_fr_description', 'conditions' => [], 'actions' => []], Argument::any())
            ->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Model\Rule');
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

    function it_throws_an_exception_when_denormalizing_a_rule_with_an_unknow_action()
    {
        $rule = [
            'code' => 'discharge_fr_description',
            'conditions' => [],
            'actions' => [
                ['type' => 'unknown_action'],
            ]
        ];

        $this->shouldThrow(
            new \LogicException('Rule "discharge_fr_description" has an unknown type of action "unknown_action".')
        )->during('denormalize', [$rule, Argument::any()]);
    }

    function it_throws_an_exception_when_denormalizing_a_rule_with_no_conditions_key()
    {
        $rule = [
            'code' => 'discharge_fr_description',
            'actions' => [
                ['type' => 'set_value'],
            ]
        ];

        $this->shouldThrow(
            new \LogicException('Rule content "discharge_fr_description" should have a "conditions" key.')
        )->during('denormalize', [$rule, Argument::any()]);
    }

    function it_throws_an_exception_when_denormalizing_a_rule_with_no_actions_key()
    {
        $rule = [
            'code' => 'discharge_fr_description',
            'conditions' => [],
        ];

        $this->shouldThrow(
            new \LogicException('Rule content "discharge_fr_description" should have a "actions" key.')
        )->during('denormalize', [$rule, Argument::any()]);
    }

    function it_throws_an_exception_when_denormalizing_a_rule_with_no_actions_type_key()
    {
        $rule = [
            'code' => 'discharge_fr_description',
            'conditions' => [],
            'actions' => [
                ['wrong' => 'set_value'],
            ]
        ];

        $this->shouldThrow(
            new \LogicException('Rule content "discharge_fr_description" has an action with no type.')
        )->during('denormalize', [$rule, Argument::any()]);
    }

    function it_throws_an_exception_when_denormalizing_a_rule_with_invalid_type_key()
    {
        $rule = [
            'code' => 'discharge_fr_description',
            'conditions' => [],
            'actions' => [
                ['type' => 'invalid'],
            ]
        ];

        $this->shouldThrow(
            new \LogicException('Rule "discharge_fr_description" has an unknown type of action "invalid".')
        )->during('denormalize', [$rule, Argument::any()]);
    }
}
