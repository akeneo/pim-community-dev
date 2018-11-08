<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Normalizer\Standard;

use Akeneo\Pim\Automation\SuggestData\Application\Normalizer\Standard\SuggestedDataNormalizer;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SuggestedDataNormalizerSpec extends ObjectBehavior
{
    public function let(
        AttributeRepositoryInterface $attributeRepository,
        MeasureConverter $measureConverter
    ): void {
        $this->beConstructedWith($attributeRepository, $measureConverter);
    }

    public function it_is_a_suggested_data_normalizer(): void
    {
        $this->shouldBeAnInstanceOf(SuggestedDataNormalizer::class);
    }

    public function it_throws_an_exception_if_attribute_does_not_exist($attributeRepository): void
    {
        $suggestedData = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];
        $attributeRepository->getAttributeTypeByCodes(['foo', 'bar'])->willReturn(
            [
                'foo' => 'pim_catalog_text',
            ]
        );

        $this->shouldThrow(new \InvalidArgumentException('Attribute with code "bar" does not exist'))
             ->during('normalize', [new SuggestedData($suggestedData)]);
    }

    public function it_throws_an_exception_for_unsupported_attribute_types($attributeRepository): void
    {
        $suggestedData = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];
        $attributeRepository->getAttributeTypeByCodes(['foo', 'bar'])->willReturn(
            [
                'foo' => 'pim_catalog_text',
                'bar' => 'pim_catalog_price_collection',
            ]
        );

        $this->shouldThrow(new \InvalidArgumentException('Unsupported attribute type "pim_catalog_price_collection"'))
             ->during('normalize', [new SuggestedData($suggestedData)]);
    }

    public function it_normalizes_suggested_data(
        AttributeInterface $attribute,
        $attributeRepository,
        $measureConverter
    ): void {
        $suggestedData = [
            'foo' => 'bar',
            'bar' => '0',
            'baz' => 'option1,option2',
            'processor' => '1 GIGAHERTZ',
        ];
        $attributeRepository->getAttributeTypeByCodes(['foo', 'bar', 'baz', 'processor'])->willReturn(
            [
                'foo' => 'pim_catalog_text',
                'bar' => 'pim_catalog_boolean',
                'baz' => 'pim_catalog_multiselect',
                'processor' => 'pim_catalog_metric',
            ]
        );

        $attribute->getMetricFamily()->willReturn('Frequency');
        $attribute->getDefaultMetricUnit()->willReturn('MEGAHERTZ');
        $attributeRepository->findOneByIdentifier('processor')->willReturn($attribute);

        $measureConverter->setFamily('Frequency')->shouldBeCalled();
        $measureConverter->convert('GIGAHERTZ', 'MEGAHERTZ', 1)->willReturn('1000');

        $this->normalize(new SuggestedData($suggestedData))->shouldReturn(
            [
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
                        'data' => false,
                    ],
                ],
                'baz' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => ['option1', 'option2'],
                    ],
                ],
                'processor' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => [
                            'amount' => '1000',
                            'unit' => 'MEGAHERTZ',
                        ],
                    ],
                ],
            ]
        );
    }
}
