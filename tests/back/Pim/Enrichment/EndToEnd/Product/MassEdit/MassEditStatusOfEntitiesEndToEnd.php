<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

class MassEditStatusOfEntitiesEndToEnd extends AbstractMassEditEndToEnd
{
    public function test_switching_status_to_entities_produces_event(): void
    {
        $this->executeMassEdit([
            'filters' => [
                [
                    'field' => 'id',
                    'operator' => Operators::IN_LIST,
                    'value' => [
                        $this->findESIdFor('1111111119', 'product'), // variant product
                        $this->findESIdFor('watch', 'product'), // product
                        /* Has 3 variant products */
                        $this->findESIdFor('amor', 'product_model'),
                    ],
                    'context' => [
                        'locale' => null,
                        'scope' => null,
                    ],
                ]
            ],
            'jobInstanceCode' => 'update_product_value',
            'actions' => [
                [
                    'field' => 'enabled',
                    'value' => false,
                ]
            ],
            'itemsCount' => 3,
            'familyVariant' => null,
            'operation' => 'change_status',
        ]);

        $this->assertEventCount(3 + 1 + 1, ProductUpdated::class);
        // A product model can not be disabled. The action is done on variant products.
        $this->assertEventCount(0, ProductModelUpdated::class);

        $product = $this->getProductWithInternalApi('watch');
        $this->assertFalse($product['enabled']);

        $variantProduct = $this->getProductWithInternalApi('1111111119');
        $this->assertFalse($variantProduct['enabled']);

        $amorVariant11 = $this->getProductWithInternalApi('1111111111');
        $this->assertFalse($amorVariant11['enabled']);
        $amorVariant12 = $this->getProductWithInternalApi('1111111112');
        $this->assertFalse($amorVariant12['enabled']);
        $amorVariant13 = $this->getProductWithInternalApi('1111111113');
        $this->assertFalse($amorVariant13['enabled']);
    }
}
