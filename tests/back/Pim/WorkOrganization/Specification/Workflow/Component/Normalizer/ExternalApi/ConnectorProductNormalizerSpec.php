<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValueNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\ExternalApi\ConnectorProductNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Routing\RouterInterface;

class ConnectorProductNormalizerSpec extends ObjectBehavior
{
    function let(ProductValueNormalizer $productValuesNormalizer, RouterInterface $router)
    {
        $this->beConstructedWith(new ValuesNormalizer($productValuesNormalizer->getWrappedObject(), $router->getWrappedObject()), new DateTimeNormalizer());
        $productValuesNormalizer->normalize(Argument::type(ValueCollection::class), 'standard')->willReturn([]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConnectorProductNormalizer::class);
    }

    function it_normalizes_a_product()
    {
        $connectorProductList = new ConnectorProductList(0, []);
        $this->normalizeConnectorProductList($connectorProductList)->shouldReturn([]);
    }

    function it_normalizes_a_list_of_products()
    {
        $connector1 = new ConnectorProduct(
            1,
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
            [
                'workflow_status' => 'working_copy'
            ],
            new ValueCollection()
        );

        $connector2 = new ConnectorProduct(
            1,
            'identifier_2',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            null,
            [],
            [],
            null,
            [],
            ['workflow_status' => 'draft_in_progress'],
            new ValueCollection()
        );

        $this->normalizeConnectorProductList(new ConnectorProductList(1, [$connector1, $connector2]))->shouldBeLike([
            [
                'identifier' => 'identifier_1',
                'created' => '2019-04-23T15:55:50+00:00',
                'updated' => '2019-04-25T15:55:50+00:00',
                'enabled' => true,
                'family' => 'family_code',
                'categories' => ['category_code_1', 'category_code_2'],
                'groups' => ['group_code_1', 'group_code_2'],
                'parent' => 'parent_product_model_code',
                'values' => (object) [],
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
                ],
                'metadata' => ['workflow_status' => 'working_copy']
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
                'values' => (object) [],
                'associations' => (object) [],
                'metadata' => ['workflow_status' => 'draft_in_progress']
            ],
        ]);
    }
}
