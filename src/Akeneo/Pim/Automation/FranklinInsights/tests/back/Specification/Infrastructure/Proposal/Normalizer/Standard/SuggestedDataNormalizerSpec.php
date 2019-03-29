<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard;

use Akeneo\Asset\Bundle\AttributeType\AttributeTypes as EnterpriseAttributeTypes;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Query\SelectAttributeOptionCodesByIdentifiersQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard\SuggestedDataNormalizer;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Pim\Automation\FranklinInsights\Specification\Builder\AttributeBuilder;
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
        MeasureConverter $measureConverter,
        SelectAttributeOptionCodesByIdentifiersQueryInterface $selectAttributeOptionCodesByIdentifiersQuery
    ): void {
        $this->beConstructedWith(
            $attributeRepository,
            $attributeOptionRepository,
            $measureConverter,
            $selectAttributeOptionCodesByIdentifiersQuery
        );
    }

    public function it_is_a_suggested_data_normalizer(): void
    {
        $this->shouldBeAnInstanceOf(SuggestedDataNormalizer::class);
    }

    public function it_normalizes_suggested_data(
        $attributeRepository,
        $measureConverter,
        $selectAttributeOptionCodesByIdentifiersQuery
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
        $attributeRepository->findByCodes(['foo', 'bar', 'baz', 'processor'])->willReturn(
            [
                (new AttributeBuilder())->withCode('foo')->withType(AttributeTypes::TEXT)->build(),
                (new AttributeBuilder())->withCode('bar')->withType(AttributeTypes::BOOLEAN)->build(),
                (new AttributeBuilder())->withCode('baz')->withType(AttributeTypes::OPTION_MULTI_SELECT)->build(),
                (new AttributeBuilder())->withCode('processor')->withType(AttributeTypes::METRIC)->build(),
            ]
        );

        $selectAttributeOptionCodesByIdentifiersQuery
            ->execute('baz', ['option1', 'option2'])
            ->willReturn(['option1', 'option2']);

        $attribute = (new AttributeBuilder())->withMetricFamily('Frequency')->withDefaultMetricUnit('MEGAHERTZ')->build();

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
        $attributeRepository->findByCodes(['foo', 'bar', 'baz'])->willReturn(
            [
                (new AttributeBuilder())->withCode('foo')->withType(AttributeTypes::TEXT)->build(),
                (new AttributeBuilder())->withCode('bar')->withType(AttributeTypes::BOOLEAN)->build(),
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
        $attributeRepository->findByCodes(['foo', 'bar', 'baz'])->willReturn(
            [
                (new AttributeBuilder())->withCode('foo')->withType(AttributeTypes::TEXT)->build(),
                (new AttributeBuilder())->withCode('bar')->withType(AttributeTypes::BOOLEAN)->build(),
                (new AttributeBuilder())->withCode('baz')->withType(AttributeTypes::OPTION_SIMPLE_SELECT)->build(),
            ]
        );
        $attributeOptionRepository->findOneByIdentifier(new AttributeCode('baz'), 'option1')->willReturn(null);

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
        $selectAttributeOptionCodesByIdentifiersQuery
    ): void {
        $suggestedData = [
            ['pimAttributeCode' => 'foo', 'value' => 'bar'],
            ['pimAttributeCode' => 'bar', 'value' => 'No'],
            ['pimAttributeCode' => 'baz', 'value' => ['option1', 'option2']],
        ];
        $attributeRepository->findByCodes(['foo', 'bar', 'baz'])->willReturn(
            [
                (new AttributeBuilder())->withCode('foo')->withType(AttributeTypes::TEXT)->build(),
                (new AttributeBuilder())->withCode('bar')->withType(AttributeTypes::BOOLEAN)->build(),
                (new AttributeBuilder())->withCode('baz')->withType(AttributeTypes::OPTION_MULTI_SELECT)->build(),
            ]
        );
        $selectAttributeOptionCodesByIdentifiersQuery->execute('baz', ['option1', 'option2'])->willReturn([]);

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
        $selectAttributeOptionCodesByIdentifiersQuery
    ): void {
        $suggestedData = [
            ['pimAttributeCode' => 'foo', 'value' => 'bar'],
            ['pimAttributeCode' => 'bar', 'value' => 'No'],
            ['pimAttributeCode' => 'baz', 'value' => ['option1', 'option2', 'option3']],
        ];
        $attributeRepository->findByCodes(['foo', 'bar', 'baz'])->willReturn(
            [
                (new AttributeBuilder())->withCode('foo')->withType(AttributeTypes::TEXT)->build(),
                (new AttributeBuilder())->withCode('bar')->withType(AttributeTypes::BOOLEAN)->build(),
                (new AttributeBuilder())->withCode('baz')->withType(AttributeTypes::OPTION_MULTI_SELECT)->build(),
            ]
        );
        $selectAttributeOptionCodesByIdentifiersQuery
            ->execute('baz', ['option1', 'option2', 'option3'])
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
        $attributeRepository->findByCodes(
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
                (new AttributeBuilder())->withCode('a_text')->withType(AttributeTypes::TEXT)->build(),
                (new AttributeBuilder())->withCode('a_date')->withType(AttributeTypes::DATE)->build(),
                (new AttributeBuilder())->withCode('a_file')->withType(AttributeTypes::FILE)->build(),
                (new AttributeBuilder())->withCode('an_image')->withType(AttributeTypes::IMAGE)->build(),
                (new AttributeBuilder())->withCode('a_price')->withType(AttributeTypes::PRICE_COLLECTION)->build(),
                (new AttributeBuilder())->withCode('a_simple_referencedata')->withType(AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT)->build(),
                (new AttributeBuilder())->withCode('a_multi_referencedata')->withType(AttributeTypes::REFERENCE_DATA_MULTI_SELECT)->build(),
                (new AttributeBuilder())->withCode('an_asset_collection')->withType(EnterpriseAttributeTypes::ASSETS_COLLECTION)->build(),
                (new AttributeBuilder())->withCode('a_simple_referenceentity')->withType(ReferenceEntityType::REFERENCE_ENTITY)->build(),
                (new AttributeBuilder())->withCode('a_multi_referenceentity')->withType(ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION)->build(),
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
