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
        $this->denormalize(['code' => 'discharge_fr_description'], Argument::any())
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
            'actions' => [
                ['type' => 'unknown_action'],
            ]
        ];

        $this->shouldThrow(
            new \LogicException('Rule "discharge_fr_description" has an unknown type of action "unknown_action".')
        )->during('denormalize', [$rule, Argument::any()]);
    }
}
