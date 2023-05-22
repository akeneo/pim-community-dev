<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness\DoubleSqlGetCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DoubleSqlGetCompletenessSpec extends ObjectBehavior
{
    function let(
        GetProductCompletenesses $legacyGetProductCompletenesses,
        GetProductCompletenesses $getProductCompletenesses,
        Connection $connection,
    ) {
        $this->beConstructedWith(
            $legacyGetProductCompletenesses,
            $getProductCompletenesses,
            $connection,
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DoubleSqlGetCompleteness::class);
    }

    function it_get_on_new_table_with_new_table_is_present(
        GetProductCompletenesses $legacyGetProductCompletenesses,
        GetProductCompletenesses $getProductCompletenesses,
        Connection $connection,
        Result $result,
    ): void
    {
        $connection->executeQuery(Argument::type('string'), Argument::type('array'))->willReturn($result);
        $result->rowCount()->willReturn(1);

        $getProductCompletenesses->fromProductUuids(Argument::type('array'), null, [])->willReturn([]);

        $uuids = [Uuid::fromString('97f53c07-4717-4385-9779-89f48c9cebe8')];

        $getProductCompletenesses->fromProductUuids($uuids, null, [])->shouldBeCalled();
        $legacyGetProductCompletenesses->fromProductUuids($uuids, null, [])->shouldNotBeCalled();

        $this->fromProductUuids($uuids);
    }

    function it_get_on_legacy_table_without_new_table_is_present(
        GetProductCompletenesses $legacyGetProductCompletenesses,
        GetProductCompletenesses $getProductCompletenesses,
        Connection $connection,
        Result $result,
    ): void
    {
        $connection->executeQuery(Argument::type('string'), Argument::type('array'))->willReturn($result);
        $result->rowCount()->willReturn(0);

        $getProductCompletenesses->fromProductUuids(Argument::type('array'), null, [])->willReturn([]);

        $uuids = [Uuid::fromString('97f53c07-4717-4385-9779-89f48c9cebe8')];

        $getProductCompletenesses->fromProductUuids($uuids, null, [])->shouldNotBeCalled();
        $legacyGetProductCompletenesses->fromProductUuids($uuids, null, [])->shouldBeCalled();

        $this->fromProductUuids($uuids);
    }
}
