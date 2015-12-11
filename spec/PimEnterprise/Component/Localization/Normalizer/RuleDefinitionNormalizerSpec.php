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
use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Presenter\PresenterInterface;
use Pim\Component\Localization\Presenter\PresenterRegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RuleDefinitionNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $ruleNormalizer,
        PresenterRegistryInterface $presenterRegistry,
        LocaleResolver $localeResolver
    ) {
        $this->beConstructedWith(
            $ruleNormalizer,
            $presenterRegistry,
            $localeResolver
        );
    }

    function it_supports_rule_definition_normalization(RuleDefinitionInterface $ruleDefinition)
    {
        $this->supportsNormalization($ruleDefinition, 'array')->shouldReturn(true);
    }

    function it_normalize_fr_numbers(
        $ruleNormalizer,
        $presenterRegistry,
        $localeResolver,
        RuleDefinitionInterface $ruleDefinition,
        PresenterInterface $pricesPresenter,
        PresenterInterface $metricPresenter,
        PresenterInterface $numberPresenter
    ) {
        $localeResolver->getCurrentLocale()->willReturn('fr_FR');
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

        $options = ['locale' => 'fr_FR'];

        $presenterRegistry->getPresenterByAttributeCode('price')->willReturn($pricesPresenter);
        $pricesPresenter->present(
            [['data' => '12.1234', 'currency' => 'EUR']],
            $options
        )->willReturn([['data' => '12,1234', 'currency' => 'EUR']]);

        $presenterRegistry->getPresenterByAttributeCode('auto_focus_points')->willReturn($numberPresenter);
        $numberPresenter->present(4.1234, $options)->willReturn('4,1234');

        $presenterRegistry->getPresenterByAttributeCode('weight')->willReturn($metricPresenter);
        $metricPresenter->present(
            ['data' => 500.1234, 'unit' => 'GRAM'],
            $options
        )->willReturn(['data' => '500,1234', 'unit' => 'GRAM']);

        $presenterRegistry->getPresenterByAttributeCode('sku')->willReturn(null);

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
