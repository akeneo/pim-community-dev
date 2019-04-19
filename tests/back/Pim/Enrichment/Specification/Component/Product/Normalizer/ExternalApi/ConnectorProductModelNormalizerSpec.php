<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValueNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectorProductModelNormalizerSpec extends ObjectBehavior
{
    function let(ProductValueNormalizer $productValuesNormalizer, RouterInterface $router)
    {
        $this->beConstructedWith(new ValuesNormalizer($productValuesNormalizer->getWrappedObject(), $router->getWrappedObject()), new DateTimeNormalizer());
        $productValuesNormalizer->normalize(Argument::type(ValueCollection::class), 'standard')->willReturn([]);
    }

    function it_is_a_normalizer_of_a_list_of_connector_products()
    {
        $this->shouldBeAnInstanceOf(ConnectorProductModelNormalizer::class);
    }

    function it_normalizes_a_list_of_connector_product_models()
    {
        $connector1 = new ConnectorProductModel(
            1,
            'code_1',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            null,
            'family_variant',
            [],
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
            ['category_code_1', 'category_code_2'],
            new ValueCollection()
        );

        $connector2 = new ConnectorProductModel(
            1,
            'code_2',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            null,
            'family_variant',
            [],
            [],
            [],
            new ValueCollection()
        );

        $this->normalizeConnectorProductModelList(new ConnectorProductModelList(1, [$connector1, $connector2]))->shouldBeLike([
            [
                'code' => 'code_1',
                'family_variant' => 'family_variant',
                'parent' => null,
                'categories' => ['category_code_1', 'category_code_2'],
                'values' => (object) [],
                'created' => '2019-04-23T15:55:50+00:00',
                'updated' => '2019-04-25T15:55:50+00:00',
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
                'code' => 'code_2',
                'family_variant' => 'family_variant',
                'parent' => [],
                'categories' => [],
                'values' => (object) [],
                'created' => '2019-04-23T15:55:50+00:00',
                'updated' => '2019-04-25T15:55:50+00:00',
                'associations' => (object) [],
            ],
        ]);
    }
}
