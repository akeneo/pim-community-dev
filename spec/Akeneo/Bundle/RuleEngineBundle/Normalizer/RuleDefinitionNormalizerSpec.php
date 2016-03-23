<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\Bundle\RuleEngineBundle\Normalizer;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RuleDefinitionNormalizerSpec extends ObjectBehavior
{
    function it_supports_rule_definition_normalization(RuleDefinitionInterface $ruleDefinition)
    {
        $this->supportsNormalization($ruleDefinition, 'array')->shouldReturn(true);
    }

    function it_normalize_numbers(RuleDefinitionInterface $ruleDefinition)
    {
        $ruleDefinition->getId()->willReturn(42);
        $ruleDefinition->getCode()->willReturn('set_tshirt_price');
        $ruleDefinition->getType()->willReturn('product');
        $ruleDefinition->getPriority()->willReturn(0);
        $ruleDefinition->getContent()->willReturn(
            [
                'conditions' => [
                    ['field' => 'sku', 'operator' => 'CONTAINS', 'value' => 'AKNTS_PB'],
                ],
                'actions' => [
                    ['type' => 'set_value', 'field' => 'price', 'value' => [
                        ['data' => '12.1234', 'currency' => 'EUR']
                    ] ],
                    ['type' => 'set_value', 'field' => 'auto_focus_points', 'value' => 4.1234 ],
                    ['type' => 'set_value', 'field' => 'weight', 'value' => [
                        'data' => 500.1234, 'unit' => 'GRAM'
                    ]],
                ]
            ]
        );

        $this->normalize($ruleDefinition, 'array', [])->shouldReturn(
            [
                'id'       => 42,
                'code'     => 'set_tshirt_price',
                'type'     => 'product',
                'priority' => 0,
                'content'  => [
                    'conditions' => [
                        ['field' => 'sku', 'operator' => 'CONTAINS', 'value' => 'AKNTS_PB'],
                    ],
                    'actions' => [
                        ['type' => 'set_value', 'field' => 'price', 'value' => [
                            ['data' => '12.1234', 'currency' => 'EUR']
                        ] ],
                        ['type' => 'set_value', 'field' => 'auto_focus_points', 'value' => 4.1234 ],
                        ['type' => 'set_value', 'field' => 'weight', 'value' => [
                            'data' => 500.1234, 'unit' => 'GRAM'
                        ]],
                    ],
                ]
            ]
        );
    }
}
