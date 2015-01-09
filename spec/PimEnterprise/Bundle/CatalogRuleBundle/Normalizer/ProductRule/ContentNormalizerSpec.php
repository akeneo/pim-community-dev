<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Normalizer\ProductRule;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ContentNormalizerSpec extends ObjectBehavior
{
    public function let(
        NormalizerInterface $conditionNormalizer,
        NormalizerInterface $setActionNormalizer,
        NormalizerInterface $copyActionNormalizer
    ) {
        $this->beConstructedWith(
            $conditionNormalizer,
            $setActionNormalizer,
            $copyActionNormalizer
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Normalizer\ProductRule\ContentNormalizer');
    }

    function it_implements()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_normalizes(
        $conditionNormalizer,
        $setActionNormalizer,
        $copyActionNormalizer,
        ProductConditionInterface $productCondition,
        ProductCopyValueActionInterface $productCopyValueAction,
        RuleInterface $rule
    ) {
        $productCondition->getField()->willReturn('family.code');
        $productCondition->getOperator()->willReturn('IN');
        $productCondition->getValue()->willReturn(['camcorders']);
        $productCondition->getLocale()->willReturn(null);
        $productCondition->getScope()->willReturn(null);

        $productCopyValueAction->getFromField()->willReturn('name');
        $productCopyValueAction->getFromLocale()->willReturn(null);
        $productCopyValueAction->getFromScope()->willReturn(null);
        $productCopyValueAction->getToField()->willReturn('camera_model_name');
        $productCopyValueAction->getToLocale()->willReturn(null);
        $productCopyValueAction->getToScope()->willReturn(null);

        $conditionNormalizer->normalize($productCondition, Argument::cetera())->shouldBeCalled()->willReturn(
            [
                'field'    => 'family.code',
                'operator' => 'IN',
                'value'    => ['camcorders']
            ]

        );
        $setActionNormalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $copyActionNormalizer->normalize($productCopyValueAction, Argument::cetera())->shouldBeCalled()->willReturn(
            [
                'from_field' => 'name',
                'to_field'   => 'camera_model_name',
                'type'       => 'copy_value'
            ]
        );

        $rule->getConditions()->willReturn([$productCondition]);
        $rule->getActions()->willReturn([$productCopyValueAction]);

        $this->normalize($rule)
            ->shouldReturn(
                [
                    'conditions' => [
                        [
                            'field'    => 'family.code',
                            'operator' => 'IN',
                            'value'    => ['camcorders']
                        ],
                    ],
                    'actions'    => [
                        [
                            'from_field' => 'name',
                            'to_field'   => 'camera_model_name',
                            'type'       => 'copy_value'
                        ],
                    ]
                ]
            );
    }

    function it_supports_normalization(RuleInterface $rule)
    {
        $rule->getType()->shouldBeCalled()->willReturn('product');

        $this->supportsNormalization($rule)->shouldReturn(true);
    }

    function it_does_not_support_normalization_for_invalid_data()
    {
        $this->supportsNormalization('invalid data')->shouldReturn(false);
    }

    function it_does_not_support_normalization_for_invalid_rule_definition_type(RuleInterface $rule)
    {
        $rule->getType()->shouldBeCalled()->willReturn('category');

        $this->supportsNormalization($rule)->shouldReturn(false);
    }

    function it_does_not_support_normalization_for_invalid_object_type(ProductInterface $rule)
    {
        $this->supportsNormalization($rule)->shouldReturn(false);
    }

    function it_throws_an_exception_when_normalizing_an_action_with_invalid_type(RuleInterface $rule, ProductInterface $product)
    {
        $rule->getConditions()->willReturn([]);
        $rule->getActions()->willReturn([$product]);
        $rule->getCode()->shouldBeCalled()->willReturn('rule_code');

        $this->shouldThrow(
            new \LogicException('Rule "rule_code" has an unknown type of action.')
        )->during('normalize', [$rule, Argument::any()]);
    }
}
