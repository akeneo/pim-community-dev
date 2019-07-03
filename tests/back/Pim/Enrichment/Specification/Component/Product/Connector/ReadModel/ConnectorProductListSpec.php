<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectorProductListSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(10, [
            new ConnectorProduct(
                1,
                'identifier',
                new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
                new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
                true,
                'family_code',
                ['category_code_1', 'category_code_2'],
                ['group_code_1', 'group_code_2'],
                'parent_product_model_code',
                [],
                [],
                new ReadValueCollection()
            )
        ]);
    }

    function it_is_a_connector_product_list()
    {
        $this->shouldBeAnInstanceOf(ConnectorProductList::class);
    }
}
