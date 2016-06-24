<?php

namespace spec\PimEnterprise\Component\CatalogRule\Connector\Processor\Normalization;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
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
}
