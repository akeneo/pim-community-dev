<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Connector\Processor\Normalization;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RuleDefinitionProcessorSpec extends ObjectBehavior
{
    function let(NormalizerInterface $ruleNormalizer)
    {
        $this->beConstructedWith($ruleNormalizer);
    }

    function it_implements()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Processor\DummyProcessor');
    }

    function it_processes_rules(
        $ruleNormalizer,
        RuleDefinitionInterface $ruleDefinition1,
        RuleDefinitionInterface $ruleDefinition2
    ) {
        $ruleDefinition1->getCode()->shouldBeCalled()->willReturn('camera_copy_name_to_model');
        $ruleDefinition2->getCode()->shouldBeCalled()->willReturn('camera_set_autofocus');

        $ruleNormalizer->normalize($ruleDefinition1)->shouldBeCalled()->willReturn(
            [
                'code' => 'camera_copy_name_to_model',
                'type' => 'product',
                'priority' => 0,
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
        $ruleNormalizer->normalize($ruleDefinition2)->shouldBeCalled()->willReturn(
            [
                'code' => 'camera_set_autofocus',
                'type' => 'product',
                'priority' => 100,
                'conditions' => [
                    [
                        'field'    => 'family.code',
                        'operator' => 'IN',
                        'value'    => ['camcorders']
                    ],
                    [
                        'field'    => 'name',
                        'operator' => 'CONTAINS',
                        'value'    => 'Canon'
                    ]
                ],
                'actions'    => [
                    [
                        'field' => 'auto_focus_lock',
                        'type'  => 'set_value',
                        'value' => true
                    ]
                ]
            ]
        );

        $item = [$ruleDefinition1, $ruleDefinition2];

        $this->process($item)->shouldReturn(
            [
                'rules' => [
                    'camera_copy_name_to_model' => [
                        'priority' => 0,
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
