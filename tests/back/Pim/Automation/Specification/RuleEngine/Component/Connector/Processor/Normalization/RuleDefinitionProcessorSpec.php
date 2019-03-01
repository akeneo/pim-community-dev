<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Normalization;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RuleDefinitionProcessorSpec extends ObjectBehavior
{
    function let(normalizerinterface $ruleNormalizer)
    {
        $this->beConstructedWith($ruleNormalizer);
    }

    function it_processes_rules(
        $ruleNormalizer,
        RuleDefinitionInterface $ruleDefinition
    ) {
        $ruleDefinition->getCode()->shouldBeCalled()->willReturn('camera_copy_name_to_model');

        $ruleNormalizer->normalize($ruleDefinition)->shouldBeCalled()->willReturn(
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

        $this->process($ruleDefinition)->shouldReturn(
            [
                'camera_copy_name_to_model' => [
                    'priority'   => 0,
                    'conditions' => [
                        [
                            'field'    => 'family.code',
                            'operator' => 'IN',
                            'value'    => ['camcorders'],
                        ],
                    ],
                    'actions'    => [
                        [
                            'from_field' => 'name',
                            'to_field'   => 'camera_model_name',
                            'type'       => 'copy_value',
                        ],
                    ],
                ],
            ]
        );
    }

    function it_sorts_conditions_on_rules(
        $ruleNormalizer,
        RuleDefinitionInterface $ruleDefinition
    ) {
        $ruleDefinition->getCode()->shouldBeCalled()->willReturn('camera_copy_name_to_model');

        $ruleNormalizer->normalize($ruleDefinition)->shouldBeCalled()->willReturn(
            [
                'code'       => 'camera_copy_name_to_model',
                'conditions' => [
                    [
                        'c' => ['camcorders'],
                        'b' => 'family.code',
                        'a' => 'IN',
                    ]
                ],
            ]
        );

        $this->process($ruleDefinition)->shouldReturn(
            [
                'camera_copy_name_to_model' => [
                    'conditions' => [
                        [
                            'a' => 'IN',
                            'b' => 'family.code',
                            'c' => ['camcorders'],
                        ]
                    ],
                ],
            ]
        );
    }

    function it_sorts_actions_on_rules(
        $ruleNormalizer,
        RuleDefinitionInterface $ruleDefinition
    ) {
        $ruleDefinition->getCode()->shouldBeCalled()->willReturn('camera_copy_name_to_model');

        $ruleNormalizer->normalize($ruleDefinition)->shouldBeCalled()->willReturn(
            [
                'code'    => 'camera_copy_name_to_model',
                'actions' => [
                    [
                        'c' => 'copy_value',
                        'b' => 'camera_model_name',
                        'a' => 'name',
                    ]
                ],
            ]
        );

        $this->process($ruleDefinition)->shouldReturn(
            [
                'camera_copy_name_to_model' => [
                    'actions' => [
                        [
                            'a' => 'name',
                            'b' => 'camera_model_name',
                            'c' => 'copy_value',
                        ]
                    ],
                ],
            ]
        );
    }
}
