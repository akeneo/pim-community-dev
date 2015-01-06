<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Serializer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductCopyValueActionNormalizer;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleConditionNormalizer;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleContentSerializerInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductSetValueActionNormalizer;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Prophecy\Argument;

class ProductRuleNormalizerSpec extends ObjectBehavior
{
    public function let(
        ProductRuleContentSerializerInterface $serializer,
        ProductRuleConditionNormalizer $conditionNormalizer,
        ProductSetValueActionNormalizer $setActionNormalizer,
        ProductCopyValueActionNormalizer $copyActionNormalizer
    ) {
        $this->beConstructedWith(
            $serializer,
            $conditionNormalizer,
            $setActionNormalizer,
            $copyActionNormalizer
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleNormalizer');
    }

    function it_implements()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_normalize(
        $serializer,
        $conditionNormalizer,
        $setActionNormalizer,
        $copyActionNormalizer,
        ProductConditionInterface $productCondition,
        ProductCopyValueActionInterface $productCopyValueAction,
        RuleDefinitionInterface $ruleDefinition
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

        $conditionNormalizer->normalize($productCondition)->shouldBeCalled()->willReturn(
            [
                'field'    => 'family.code',
                'operator' => 'IN',
                'value'    => ['camcorders']
            ]

        );
        $setActionNormalizer->normalize(Argument::any())->shouldNotBeCalled();
        $copyActionNormalizer->normalize($productCopyValueAction)->shouldBeCalled()->willReturn(
            [
                'from_field' => 'name',
                'to_field'   => 'camera_model_name',
                'type'       => 'copy_value'
            ]
        );

        $ruleDefinition
            ->getContent()
            ->shouldBeCalled()
            ->willReturn(
                '{"conditions":[{"field":"family.code","operator":"IN","value":["camcorders"]}],"actions":[{"type":"copy_value","from_field":"name","to_field":"camera_model_name"}]}'
            );

        $serializer
            ->deserialize(
                '{"conditions":[{"field":"family.code","operator":"IN","value":["camcorders"]}],"actions":[{"type":"copy_value","from_field":"name","to_field":"camera_model_name"}]}'
            )->shouldBeCalled()
            ->willReturn(
                [
                    'conditions' => [$productCondition],
                    'actions'    => [$productCopyValueAction]
                ]
            );
        $this->normalize($ruleDefinition)
            ->shouldReturn(
                [
                    'conditions' => [
                        [
                            'field'    => 'family.code',
                            'operator' => 'IN',
                            'value'    => ['camcorders']
                        ]
                    ],
                    'actions'    => [
                        [
                            'from_field' => 'name',
                            'to_field'   => 'camera_model_name',
                            'type'       => 'copy_value'
                        ]
                    ]
                ]
            );
    }

    function it_supports_normalization(RuleDefinitionInterface $ruleDefinition)
    {
        $ruleDefinition->getType()->shouldBeCalled()->willReturn('product');

        $this->supportsNormalization($ruleDefinition)->shouldReturn(true);
    }

    function it_does_not_support_normalization_for_invalid_data()
    {
        $this->supportsNormalization('invalid data')->shouldReturn(false);
    }

    function it_does_not_support_normalization_for_invalid_rule_definition_type(RuleDefinitionInterface $ruleDefinition)
    {
        $ruleDefinition->getType()->shouldBeCalled()->willReturn('category');

        $this->supportsNormalization($ruleDefinition)->shouldReturn(false);
    }

    function it_does_not_support_normalization_for_invalid_object_type(ProductInterface $ruleDefinition)
    {
        $this->supportsNormalization($ruleDefinition)->shouldReturn(false);
    }

    function it_throws_an_exception_when_normalizing_an_object_with_invalid_type($serializer, RuleDefinitionInterface $ruleDefinition, ProductInterface $product)
    {
        $ruleDefinition->getContent()->shouldBeCalled()->willReturn('{data}');
        $ruleDefinition->getCode()->shouldBeCalled()->willReturn('rule_code');
        $serializer->deserialize('{data}')->shouldBeCalled()->willReturn(['conditions' => [], 'actions' => [$product]]);

        $this->shouldThrow(
            new \LogicException('Rule "rule_code" has an unknown type of action.')
        )->during('normalize', [$ruleDefinition, Argument::any()]);
    }
}
