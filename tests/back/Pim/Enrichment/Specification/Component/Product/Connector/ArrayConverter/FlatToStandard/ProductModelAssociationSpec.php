<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ProductModel;

class ProductModelAssociationSpec extends ObjectBehavior
{
    function let(ProductModel $productConverter)
    {
        $this->beConstructedWith($productConverter);
    }

    function it_converts($productConverter)
    {
        $item = [
            'code' => 'hiroshima',
            'name-fr_FR' => 'T-shirt super beau',
            'description-en_US-mobile' => 'My description',
            'length' => '10 CENTIMETER',
            'XSELL-groups' => 'akeneo_tshirt, oro_tshirt',
            'XSELL-product' => 'AKN_TS, ORO_TSH',
        ];

        $resultItem = [
            'code' => 'hiroshima',
            'enabled' => true,
            'values' => [
                'name' => [['locale' => 'en_US', 'scope' => null, 'data' => 'name']],
                'description' => [['locale' => 'en_US', 'scope' => 'ecommerce', 'data' => 'description']],
            ],
            'associations' => [
                'XSELL' => [
                    'groups' => ['akeneo_tshirt', 'oro_tshirt'],
                    'products' => ['AKN_TS', 'ORO_TSH'],
                ],
            ],
        ];

        $productConverter->convert($item, [])->willReturn($resultItem);

        $filteredItem = [
            "code" => "hiroshima",
            "associations" => [
                "XSELL" => [
                    "groups" => ["akeneo_tshirt", "oro_tshirt"],
                    "products" => ["AKN_TS", "ORO_TSH"],
                ],
            ],
        ];

        $this
            ->convert($item, [])
            ->shouldReturn($filteredItem);
    }
}
