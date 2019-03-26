<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Normalizer\Standard;

use Akeneo\Asset\Bundle\AttributeType\AttributeTypes as EnterpriseAttributeTypes;
use Akeneo\Pim\Automation\FranklinInsights\Application\Normalizer\Standard\SuggestedDataNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
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
            ->willReturn([
                ['code' => 'option1'],
                ['code' => 'option2']
            ]);

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
            ->willReturn([
                ['code' => 'option1'],
                ['code' => 'option3']
            ]);

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
            ['pimAttributeCode' => 'a_text', 'value' => 'bar'],
            ['pimAttributeCode' => 'a_date', 'value' => '2018-12-25 00:00:00'],
            ['pimAttributeCode' => 'a_file', 'value' => '/some/file/path.csv'],
            ['pimAttributeCode' => 'an_image', 'value' => '/images/unicorn.png'],
            ['pimAttributeCode' => 'a_price', 'value' => ['25 €', '30 $']],
            ['pimAttributeCode' => 'a_simple_referencedata', 'value' => 'black'],
            ['pimAttributeCode' => 'a_multi_referencedata', 'value' => ['white', 'purple']],
            ['pimAttributeCode' => 'an_asset_collection', 'value' => ['asset1', 'asset2']],
            ['pimAttributeCode' => 'a_simple_referenceentity', 'value' => 'philippestarck'],
            ['pimAttributeCode' => 'a_multi_referenceentity', 'value' => ['nantes', 'boston', 'telaviv', 'dusseldorf']],
        ];
        $attributeRepository->getAttributeTypeByCodes(
            [
                'a_text',
                'a_date',
                'a_file',
                'an_image',
                'a_price',
                'a_simple_referencedata',
                'a_multi_referencedata',
                'an_asset_collection',
                'a_simple_referenceentity',
                'a_multi_referenceentity',
            ]
        )->willReturn(
            [
                'a_text' => AttributeTypes::TEXT,
                'a_date' => AttributeTypes::DATE,
                'a_file' => AttributeTypes::FILE,
                'an_image' => AttributeTypes::IMAGE,
                'a_price' => AttributeTypes::PRICE_COLLECTION,
                'a_simple_referencedata' => AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT,
                'a_multi_referencedata' => AttributeTypes::REFERENCE_DATA_MULTI_SELECT,
                'an_asset_collection' => EnterpriseAttributeTypes::ASSETS_COLLECTION,
                'a_simple_referenceentity' => ReferenceEntityType::REFERENCE_ENTITY,
                'a_multi_referenceentity' => ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION,
            ]
        );

        $this->normalize(new SuggestedData($suggestedData))->shouldReturn(
            [
                'a_text' => [
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
