<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Normalizer\Standard;

use Akeneo\Pim\Automation\SuggestData\Application\Normalizer\Standard\SuggestedDataNormalizer;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
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
        AttributeOptionRepositoryInterface $attributeOptionRepository,
        MeasureConverter $measureConverter
    ): void {
        $this->beConstructedWith($attributeRepository, $attributeOptionRepository, $measureConverter);
    }

    public function it_is_a_suggested_data_normalizer(): void
    {
        $this->shouldBeAnInstanceOf(SuggestedDataNormalizer::class);
    }

    public function it_normalizes_suggested_data(
        $attributeRepository,
        $measureConverter,
        $attributeOptionRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedData = [
            [
                'pimAttributeCode' => 'foo',
                'value' => 'bar',
            ],
            [
                'pimAttributeCode' => 'bar',
                'value' => 'No',
            ],
            [
                'pimAttributeCode' => 'baz',
                'value' => ['option1', 'option2'],
            ],
            [
                'pimAttributeCode' => 'processor',
                'value' => '1 GIGAHERTZ',
            ],
        ];
        $attributeRepository->getAttributeTypeByCodes(['foo', 'bar', 'baz', 'processor'])->willReturn(
            [
                'foo' => 'pim_catalog_text',
                'bar' => 'pim_catalog_boolean',
                'baz' => 'pim_catalog_multiselect',
                'processor' => 'pim_catalog_metric',
            ]
        );

        $attributeOptionRepository
            ->findCodesByIdentifiers('baz', ['option1', 'option2'])
            ->willReturn(['option1', 'option2']);

        $attribute->getMetricFamily()->willReturn('Frequency');
        $attribute->getDefaultMetricUnit()->willReturn('MEGAHERTZ');
        $attribute->isDecimalsAllowed()->willReturn(false);
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
                            'amount' => 1000,
                            'unit' => 'MEGAHERTZ',
                        ],
                    ],
                ],
            ]
        );
    }

    public function it_normalizes_suggested_data_ignoring_not_existing_attributes(
        $attributeRepository
    ): void {
        $suggestedData = [
            ['pimAttributeCode' => 'foo', 'value' => 'bar'],
            ['pimAttributeCode' => 'bar', 'value' => 'No'],
            ['pimAttributeCode' => 'baz', 'value' => ['option1', 'option2']],
        ];
        $attributeRepository->getAttributeTypeByCodes(['foo', 'bar', 'baz'])->willReturn(
            [
                'foo' => 'pim_catalog_text',
                'bar' => 'pim_catalog_boolean',
            ]
        );

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
            ]
        );
    }

    public function it_normalizes_suggested_data_ignoring_simple_select_if_option_does_not_exist(
        $attributeRepository,
        $attributeOptionRepository
    ): void {
        $suggestedData = [
            ['pimAttributeCode' => 'foo', 'value' => 'bar'],
            ['pimAttributeCode' => 'bar', 'value' => 'No'],
            ['pimAttributeCode' => 'baz', 'value' => 'option1'],
        ];
        $attributeRepository->getAttributeTypeByCodes(['foo', 'bar', 'baz'])->willReturn(
            [
                'foo' => 'pim_catalog_text',
                'bar' => 'pim_catalog_boolean',
                'baz' => 'pim_catalog_simpleselect',
            ]
        );
        $attributeOptionRepository->findOneByIdentifier('baz.option1')->willReturn(null);

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
            ]
        );
    }

    public function it_normalizes_suggested_data_ignoring_multi_select_if_no_option_at_all_exists(
        $attributeRepository,
        $attributeOptionRepository
    ): void {
        $suggestedData = [
            ['pimAttributeCode' => 'foo', 'value' => 'bar'],
            ['pimAttributeCode' => 'bar', 'value' => 'No'],
            ['pimAttributeCode' => 'baz', 'value' => ['option1', 'option2']],
        ];
        $attributeRepository->getAttributeTypeByCodes(['foo', 'bar', 'baz'])->willReturn(
            [
                'foo' => 'pim_catalog_text',
                'bar' => 'pim_catalog_boolean',
                'baz' => 'pim_catalog_multiselect',
            ]
        );
        $attributeOptionRepository->findCodesByIdentifiers('baz', ['option1', 'option2'])->willReturn([]);

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
            ]
        );
    }

    public function it_normalizes_suggested_data_ignoring_multi_select_options_that_do_not_exist(
        $attributeRepository,
        $attributeOptionRepository
    ): void {
        $suggestedData = [
            ['pimAttributeCode' => 'foo', 'value' => 'bar'],
            ['pimAttributeCode' => 'bar', 'value' => 'No'],
            ['pimAttributeCode' => 'baz', 'value' => ['option1', 'option2', 'option3']],
        ];
        $attributeRepository->getAttributeTypeByCodes(['foo', 'bar', 'baz'])->willReturn(
            [
                'foo' => 'pim_catalog_text',
                'bar' => 'pim_catalog_boolean',
                'baz' => 'pim_catalog_multiselect',
            ]
        );
        $attributeOptionRepository
            ->findCodesByIdentifiers('baz', ['option1', 'option2', 'option3'])
            ->willReturn(['option1', 'option3']);

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
                        'data' => ['option1', 'option3'],
                    ],
                ],
            ]
        );
    }

    public function it_normalizes_suggested_data_ignoring_unsupported_attribute_types($attributeRepository): void
    {
        $suggestedData = [
            ['pimAttributeCode' => 'foo', 'value' => 'bar'],
            ['pimAttributeCode' => 'bar', 'value' => 'baz'],
        ];
        $attributeRepository->getAttributeTypeByCodes(['foo', 'bar'])->willReturn(
            [
                'foo' => 'pim_catalog_text',
                'bar' => 'pim_catalog_price_collection',
            ]
        );

        $this->normalize(new SuggestedData($suggestedData))->shouldReturn(
            [
                'foo' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'bar',
                    ],
                ],
            ]
        );
    }
}
