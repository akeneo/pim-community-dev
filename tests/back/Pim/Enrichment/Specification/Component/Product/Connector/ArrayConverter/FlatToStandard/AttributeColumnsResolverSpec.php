<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;

class AttributeColumnsResolverSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        CurrencyRepositoryInterface $currencyRepository,
        AttributeValuesResolverInterface $valuesResolver
    ) {
        $this->beConstructedWith($attributeRepository, $currencyRepository, $valuesResolver);
    }

    function it_resolves_identifier_field($attributeRepository)
    {
        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $this->resolveIdentifierField()->shouldReturn('sku');
    }

    function it_resolves_attributes_fields(
        $attributeRepository,
        $currencyRepository,
        $valuesResolver,
        AttributeInterface $sku,
        AttributeInterface $name
    ) {
        $attributeRepository->findAll()->willReturn([$sku, $name]);
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['USD', 'EUR']);

        $valuesResolver->resolveEligibleValues([$sku, $name])
            ->willReturn(
                [
                    [
                        'attribute' => 'sku',
                        'type' => 'pim_catalog_identifier',
                        'locale' => null,
                        'scope' => null
                    ],
                    [
                        'attribute' => 'name',
                        'type' => 'pim_catalog_text',
                        'locale' => 'en_US',
                        'scope' => null
                    ],
                    [
                        'attribute' => 'name',
                        'type' => 'pim_catalog_text',
                        'locale' => 'fr_FR',
                        'scope' => null
                    ],
                    [
                        'attribute' => 'price',
                        'type' => 'pim_catalog_price_collection',
                        'locale' => null,
                        'scope' => null
                    ],
                ]
            );

        $this->resolveAttributeColumns()
            ->shouldReturn(
                [
                    'sku',
                    'name-en_US',
                    'name-fr_FR',
                    'price',
                    'price-USD',
                    'price-EUR'
                ]
            );
    }

    function it_resolves_flat_attribute_name()
    {
        $expected1 = 'description-en_US-mobile';
        $expected2 = 'name-ecommerce';
        $expected3 = 'weight-fr_FR';
        $expected4 = 'sku';

        $this->resolveFlatAttributeName('description', 'en_US', 'mobile')->shouldReturn($expected1);
        $this->resolveFlatAttributeName('name', null, 'ecommerce')->shouldReturn($expected2);
        $this->resolveFlatAttributeName('weight', 'fr_FR', null)->shouldReturn($expected3);
        $this->resolveFlatAttributeName('sku', null, null)->shouldReturn($expected4);
    }
}
