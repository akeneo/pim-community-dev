<?php

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Normalizer\Standard;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Normalizer\Standard\SuggestedDataNormalizer;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SuggestedDataNormalizerSpec extends ObjectBehavior
{
    public function let(ArrayConverterInterface $converter)
    {
        $this->beConstructedWith($converter);
    }

    public function it_is_a_suggested_data_normalizer()
    {
        $this->shouldBeAnInstanceOf(SuggestedDataNormalizer::class);
    }

    public function it_normalizes_suggested_data($converter)
    {
        $suggestedData = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];
        $expected = [
            'foo' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => 'bar',
                ],
            ],
            'bar' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => 'baz',
                ],
            ],
        ];

        $converter->convert($suggestedData)->willReturn($expected);
        $this->normalize(new SuggestedData($suggestedData))->shouldReturn($expected);
    }

    public function it_does_not_normalize_values_for_scopable_or_localizable_attributes($converter)
    {
        $suggestedData = [
            'localizable-fr_FR' => 'foo',
            'scopable-ecommerce' => 'bar',
            'baz' => '42',
        ];
        $converter->convert($suggestedData)->willReturn(
            [
                'localizable' => [
                    [
                        'scope' => null,
                        'locale' => 'fr_FR',
                        'data' => 'foo',
                    ],
                ],
                'scopable' => [
                    [
                        'scope' => 'ecommerce',
                        'locale' => null,
                        'data' => 'bar',
                    ],
                ],
                'baz' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => '42',
                    ],
                ],
            ]
        );
        $this->normalize(new SuggestedData($suggestedData))->shouldReturn(
            [
                'baz' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => '42',
                    ],
                ],
            ]
        );
    }
}
