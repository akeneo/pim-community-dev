<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Grid\Query;

use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductProperties;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductPropertiesRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use PhpSpec\ObjectBehavior;

class AddAdditionalProductPropertiesRegistrySpec extends ObjectBehavior
{
    function let(AddAdditionalProductProperties $productPropertiesQuery1, AddAdditionalProductProperties $productPropertiesQuery2)
    {
        $this->beConstructedWith([$productPropertiesQuery1, $productPropertiesQuery2]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddAdditionalProductPropertiesRegistry::class);
    }

    function it_add_a_properties_into_the_rows(
        ProductQueryBuilderInterface $productQueryBuilder,
        $productPropertiesQuery1,
        $productPropertiesQuery2
    ) {
        $queryParameters = new FetchProductAndProductModelRowsParameters(
            $productQueryBuilder->getWrappedObject(),
            ['attribute_1', 'attribute_2'],
            'ecommerce',
            'en_US'
        );

        $rows = [
            Row::fromProduct(
                'identifier_1',
                'family label',
                ['group_1', 'group_2'],
                true,
                new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
                new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
                'data',
                null,
                90,
                1,
                'parent_code',
                new WriteValueCollection([])
            ),
            Row::fromProduct(
                'identifier_2',
                'family label',
                ['group_1', 'group_2'],
                true,
                new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
                new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
                'data',
                null,
                90,
                1,
                'parent_code',
                new WriteValueCollection([])
            ),
        ];

        $rowsWithAdditionalProperty = [
            $rows[0]->addAdditionalProperty(new AdditionalProperty('name', 'value_1')),
            $rows[0]->addAdditionalProperty(new AdditionalProperty('name', 'value_2')),
        ];

        $productPropertiesQuery1->add($queryParameters, $rows)->willReturn($rows);
        $productPropertiesQuery2->add($queryParameters, $rows)->willReturn($rowsWithAdditionalProperty);

        $this->add($queryParameters, $rows)->shouldReturn($rowsWithAdditionalProperty);
    }

}
