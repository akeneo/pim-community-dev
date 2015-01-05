<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Connector;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleContentSerializerInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Prophecy\Argument;

class YamlProcessorSpec extends ObjectBehavior
{
    public function let(ProductRuleContentSerializerInterface $serializer)
    {
        $this->beConstructedWith($serializer);
    }

    function it_implements()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Processor\DummyProcessor');
    }

    function it_processes_rules(
        $serializer,
        RuleDefinition $ruleDefinition1,
        RuleDefinition $ruleDefinition2,
        ProductConditionInterface $productCondition1,
        ProductConditionInterface $productCondition2,
        ProductSetValueActionInterface $productSetValueAction,
        ProductCopyValueActionInterface $productCopyValueAction
    ) {
        $ruleDefinition1->getCode()->shouldBeCalled()->willReturn('camera_copy_name_to_model');
        $ruleDefinition2->getCode()->shouldBeCalled()->willReturn('camera_set_autofocus');

        $productCondition1->getField()->willReturn('family.code');
        $productCondition1->getOperator()->willReturn('IN');
        $productCondition1->getValue()->willReturn(['camcorders']);
        $productCondition1->getLocale()->willReturn(null);
        $productCondition1->getScope()->willReturn(null);

        $productCondition2->getField()->willReturn('name');
        $productCondition2->getOperator()->willReturn('CONTAINS');
        $productCondition2->getValue()->willReturn('Canon');
        $productCondition2->getLocale()->willReturn(null);
        $productCondition2->getScope()->willReturn(null);

        $productCopyValueAction->getFromField()->willReturn('name');
        $productCopyValueAction->getFromLocale()->willReturn(null);
        $productCopyValueAction->getFromScope()->willReturn(null);
        $productCopyValueAction->getToField()->willReturn('camera_model_name');
        $productCopyValueAction->getToLocale()->willReturn(null);
        $productCopyValueAction->getToScope()->willReturn(null);

        $productSetValueAction->getField()->willReturn('auto_focus_lock');
        $productSetValueAction->getValue()->willReturn(true);
        $productSetValueAction->getScope()->willReturn(null);
        $productSetValueAction->getLocale()->willReturn(null);

        $ruleDefinition1
            ->getContent()
            ->shouldBeCalled()
            ->willReturn(
                '{"conditions":[{"field":"family.code","operator":"IN","value":["camcorders"]}],"actions":[{"type":"copy_value","from_field":"name","to_field":"camera_model_name"}]}'
            );
        $ruleDefinition1->getPriority()->shouldBeCalled()->willReturn(0);

        $ruleDefinition2
            ->getContent()
            ->shouldBeCalled()
            ->willReturn(
                '{"conditions":[{"field":"family.code","operator":"IN","value":["camcorders"]},{"field":"name","operator":"CONTAINS","value":"Canon"}],"actions":[{"type":"set_value","field":"auto_focus_lock","value":true}]}'
            );
        $ruleDefinition2->getPriority()->shouldBeCalled()->willReturn(100);

        $serializer
            ->deserialize(
                '{"conditions":[{"field":"family.code","operator":"IN","value":["camcorders"]}],"actions":[{"type":"copy_value","from_field":"name","to_field":"camera_model_name"}]}'
            )->shouldBeCalled()
            ->willReturn(
                [
                    'conditions' => [$productCondition1],
                    'actions'    => [$productCopyValueAction]
                ]
            );

        $serializer
            ->deserialize(
                '{"conditions":[{"field":"family.code","operator":"IN","value":["camcorders"]},{"field":"name","operator":"CONTAINS","value":"Canon"}],"actions":[{"type":"set_value","field":"auto_focus_lock","value":true}]}'
            )->shouldBeCalled()
            ->willReturn(
                [
                    'conditions' => [$productCondition1, $productCondition2],
                    'actions'    => [$productSetValueAction]
                ]
            );

        $item = [$ruleDefinition1, $ruleDefinition2];

        $this->process($item)->shouldReturn(
            [
                'rules' => [
                    'camera_copy_name_to_model' => [
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
                    ],
                    'camera_set_autofocus'      => [
                        'priority' => 100,
                        'conditions' => [
                            [
                                'field'    => 'family.code',
                                'operator' => 'IN',
                                'value'    => ['camcorders']
                            ],
                            [
                                'field' => 'name',
                                'operator' => 'CONTAINS',
                                'value' => 'Canon'
                            ]
                        ],
                        'actions' => [
                            [
                                'field' => 'auto_focus_lock',
                                'type'  => 'set_value',
                                'value' => true
                            ]
                        ]
                    ]
                ]
            ]
        );
    }
}
