<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValuesNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConnectorProductNormalizerSpec extends ObjectBehavior
{
    function let(ProductValuesNormalizer $valuesNormalizer)
    {
        $this->beConstructedWith($valuesNormalizer, new DateTimeNormalizer());
        $valuesNormalizer->normalize(Argument::type(ValueCollection::class))->willReturn([]);

    }

    function it_is_a_normalizer_of_a_list_of_connector_products()
    {
        $this->shouldBeAnInstanceOf(ConnectorProductNormalizer::class);
    }

    function it_normalizes_a_list_of_products()
    {
        $connector1 = new ConnectorProduct(
            'identifier_1',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            'family_code',
            ['category_code_1', 'category_code_2'],
            ['group_code_1', 'group_code_2'],
            'parent_product_model_code',
            [
                'X_SELL' => [
                    'products' => ['product_code_1'],
                    'product_models' => [],
                    'groups' => ['group_code_2']
                ],
                'UPSELL' => [
                    'products' => ['product_code_4'],
                    'product_models' => ['product_model_5'],
                    'groups' => ['group_code_3']
                ]
            ],
            [],
            new ValueCollection()
        );

        $connector2 = new ConnectorProduct(
            'identifier_2',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            null,
            [],
            [],
            null,
            [],
            [],
            new ValueCollection()
        );

        $this->normalizeConnectorProductList(new ConnectorProductList(1, [$connector1, $connector2]))->shouldReturn([
            [
                'identifier' => 'identifier_1',
                'created' => '2019-04-23T15:55:50+00:00',
                'updated' => '2019-04-25T15:55:50+00:00',
                'enabled' => true,
                'family' => 'family_code',
                'categories' => ['category_code_1', 'category_code_2'],
                'groups' => ['group_code_1', 'group_code_2'],
                'parent' => 'parent_product_model_code',
                'values' => [],
                'associations' => [
                    'X_SELL' => [
                        'products' => ['product_code_1'],
                        'product_models' => [],
                        'groups' => ['group_code_2']
                    ],
                    'UPSELL' => [
                        'products' => ['product_code_4'],
                        'product_models' => ['product_model_5'],
                        'groups' => ['group_code_3']
                    ]
                ]
            ],
            [
                'identifier' => 'identifier_2',
                'created' => '2019-04-23T15:55:50+00:00',
                'updated' => '2019-04-25T15:55:50+00:00',
                'enabled' => true,
                'family' => null,
                'categories' => [],
                'groups' => [],
                'parent' => null,
                'values' => [],
                'associations' => []
            ],
        ]);
    }
}
