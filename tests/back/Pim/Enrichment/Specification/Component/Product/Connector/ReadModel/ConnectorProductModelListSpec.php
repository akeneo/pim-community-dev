<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectorProductModelListSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(1, [
            new ConnectorProductModel(
                12345,
                'code',
                new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
                new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
                null,
                'family',
                'family_variant',
                ['workflow_status' => 'in_progress'],
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
                new ReadValueCollection()
            )
        ]);
    }

    function it_is_a_connector_product_model()
    {
        $this->shouldHaveType(ConnectorProductModelList::class);
    }
}
