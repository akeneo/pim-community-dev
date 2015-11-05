<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\PimEnterprise\Component\Localization\Normalizer;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Localization\Localizer\LocalizedAttributeConverterInterface;
use Pim\Component\Localization\Provider\Format\DateFormatProvider;
use Pim\Component\Localization\Provider\Format\NumberFormatProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RuleDefinitionNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $ruleNormalizer,
        LocalizedAttributeConverterInterface $converter,
        RequestStack $requestStack,
        NumberFormatProvider $numberFormatProvider,
        DateFormatProvider $dateFormatProvider
    ) {
        $this->beConstructedWith(
            $ruleNormalizer,
            $converter,
            $requestStack,
            $numberFormatProvider,
            $dateFormatProvider
        );
    }

    function it_supports_rule_definition_normalization(RuleDefinitionInterface $ruleDefinition)
    {
        $this->supportsNormalization($ruleDefinition, 'array')->shouldReturn(true);
    }

    function it_normalize_fr_numbers(
        RuleDefinitionInterface $ruleDefinition,
        NumberFormatProvider $numberFormatProvider,
        DateFormatProvider $dateFormatProvider,
        RequestStack $requestStack,
        Request $currentRequest,
        NormalizerInterface $ruleNormalizer,
        LocalizedAttributeConverterInterface $converter
    ) {
        $requestStack->getCurrentRequest()->willReturn($currentRequest);
        $currentRequest->getLocale()->willReturn('fr_FR');
        $numberFormatProvider->getFormat('fr_FR')->willReturn(['decimal_separator' => ',']);
        $dateFormatProvider->getFormat('fr_FR')->willReturn('d/m/Y');
        $ruleNormalizer->normalize($ruleDefinition, 'array', [])->willReturn(
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

        $localeOptions = ['decimal_separator' => ',', 'date_format' => 'd/m/Y'];

        $converter->convertDefaultToLocalizedValue(
            'price',
            [['data' => '12.1234', 'currency' => 'EUR']],
            $localeOptions
        )->willReturn([['data' => '12,1234', 'currency' => 'EUR']]);

        $converter->convertDefaultToLocalizedValue('auto_focus_points', 4.1234, $localeOptions)->willReturn('4,1234');

        $converter->convertDefaultToLocalizedValue(
            'weight',
            ['data' => 500.1234, 'unit' => 'GRAM'],
            $localeOptions
        )->willReturn(['data' => '500,1234', 'unit' => 'GRAM']);

        $converter->convertDefaultToLocalizedValue('sku', 'AKNTS_PB', $localeOptions)->willReturn('AKNTS_PB');

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
                            ['data' => '12,1234', 'currency' => 'EUR']
                        ] ],
                        ['type' => 'set_value', 'field' => 'auto_focus_points', 'value' => '4,1234' ],
                        ['type' => 'set_value', 'field' => 'weight', 'value' => [
                            'data' => '500,1234', 'unit' => 'GRAM'
                        ]],
                    ],
                ]
            ]
        );
    }
}
