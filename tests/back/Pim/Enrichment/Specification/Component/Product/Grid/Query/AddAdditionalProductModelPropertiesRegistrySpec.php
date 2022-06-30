<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Grid\Query;

use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductModelProperties;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductModelPropertiesRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use PhpSpec\ObjectBehavior;

class AddAdditionalProductModelPropertiesRegistrySpec extends ObjectBehavior
{
    function let(AddAdditionalProductModelProperties $productModelPropertiesQuery1, AddAdditionalProductModelProperties $productModelPropertiesQuery2)
    {
        $this->beConstructedWith([$productModelPropertiesQuery1, $productModelPropertiesQuery2]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddAdditionalProductModelPropertiesRegistry::class);
    }

    function it_add_a_properties_into_the_rows(
        ProductQueryBuilderInterface $productQueryBuilder,
        $productModelPropertiesQuery1,
        $productModelPropertiesQuery2
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
                '54162e35-ff81-48f1-96d5-5febd3f00fd5',
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
                'd9f573cc-8905-4949-8151-baf9d5328f26',
                'parent_code',
                new WriteValueCollection([])
            ),
        ];

        $rowsWithAdditionalProperty = [
            $rows[0]->addAdditionalProperty(new AdditionalProperty('name', 'value_1')),
            $rows[0]->addAdditionalProperty(new AdditionalProperty('name', 'value_2')),
        ];

        $productModelPropertiesQuery1->add($queryParameters, $rows)->willReturn($rows);
        $productModelPropertiesQuery2->add($queryParameters, $rows)->willReturn($rowsWithAdditionalProperty);

        $this->add($queryParameters, $rows)->shouldReturn($rowsWithAdditionalProperty);
    }

}
