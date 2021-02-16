<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Symfony\Component\HttpFoundation\Response;

class MassEditCategoriesOfEntitiesEndToEnd extends AbstractMassEditEndToEnd
{
    public function test_adding_a_category_to_entities_produces_event(): void
    {
        $this->executeMassEdit([
            'filters' => [
                [
                    'field' => 'id',
                    'operator' => Operators::IN_LIST,
                    'value' => [
                        $this->findESIdFor('1111111111', 'product'), // variant product
                        $this->findESIdFor('watch', 'product'), // product
                        $this->findESIdFor('apollon_yellow', 'product_model'),
                    ],
                    'context' => [
                        'locale' => null,
                        'scope' => null,
                    ],
                ]
            ],
            'jobInstanceCode' => 'add_to_category',
            'actions' => [
                [
                    'field' => 'categories',
                    'value' => ['master_men_pants_jeans'],
                ]
            ],
            'itemsCount' => 3,
            'familyVariant' => null,
            'operation' => 'add_to_category',
        ]);

        $this->assertEventCount(2, ProductUpdated::class);
        $this->assertEventCount(1, ProductModelUpdated::class);
    }

    public function test_removing_a_category_to_entities_produces_event(): void
    {
        $response = $this->updateProductWithInternalApi('1111111119', [
            'identifier' => '1111111119',
            'values' => [],
            'categories' => ['print_accessories'],
        ]);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->clearMessengerTransport();

        $this->executeMassEdit([
            'filters' => [
                [
                    'field' => 'id',
                    'operator' => Operators::IN_LIST,
                    'value' => [
                        $this->findESIdFor('1111111119', 'product'), // variant product
                        $this->findESIdFor('1111111171', 'product'), // product
                        $this->findESIdFor('amor', 'product_model'),
                    ],
                    'context' => [
                        'locale' => null,
                        'scope' => null,
                    ],
                ]
            ],
            'jobInstanceCode' => 'remove_from_category',
            'actions' => [
                [
                    'field' => 'categories',
                    'value' => ['master_men_blazers', 'print_accessories'],
                ]
            ],
            'itemsCount' => 3,
            'familyVariant' => null,
            'operation' => 'remove_from_category',
        ]);

        $this->assertEventCount(2, ProductUpdated::class);
        $this->assertEventCount(1, ProductModelUpdated::class);
    }
}
