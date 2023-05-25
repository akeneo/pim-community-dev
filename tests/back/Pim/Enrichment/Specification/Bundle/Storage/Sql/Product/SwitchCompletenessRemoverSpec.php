<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\SwitchCompletenessRemover;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\ProductCompletenessRemoverInterface;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Doctrine\DBAL\Connection;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SwitchCompletenessRemoverSpec extends ObjectBehavior
{
    function let(
        ProductCompletenessRemoverInterface $legacyCompletenessRemover,
        ProductCompletenessRemoverInterface $productCompletenessRemover,
        Connection $connection,
    ) {
        $this->beConstructedWith(
            $legacyCompletenessRemover,
            $productCompletenessRemover,
            $connection,
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SwitchCompletenessRemover::class);
    }

    function it_remove_on_new_table_with_new_table_is_present(
        ProductCompletenessRemoverInterface $legacyCompletenessRemover,
        ProductCompletenessRemoverInterface $productCompletenessRemover,
        Connection $connection,
        Result $result,
    ): void
    {
        $connection->executeQuery(Argument::type('string'), Argument::type('array'))->willReturn($result);
        $result->rowCount()->willReturn(1);

        $productCompletenessRemover->deleteForProducts(Argument::type('array'), null, [])->willReturn(1);

        $uuids = [Uuid::fromString('97f53c07-4717-4385-9779-89f48c9cebe8')];

        $productCompletenessRemover->deleteForProducts($uuids)->shouldBeCalled();
        $legacyCompletenessRemover->deleteForProducts($uuids)->shouldBeCalled();

        $this->deleteForProducts($uuids);
    }

    function it_remove_on_legacy_table_without_new_table_is_present(
        ProductCompletenessRemoverInterface $legacyCompletenessRemover,
        ProductCompletenessRemoverInterface $productCompletenessRemover,
        Connection $connection,
        Result $result,
    ): void
    {
        $connection->executeQuery(Argument::type('string'), Argument::type('array'))->willReturn($result);
        $result->rowCount()->willReturn(0);

        $productCompletenessRemover->deleteForProducts(Argument::type('array'), null, [])->willReturn(1);

        $uuids = [Uuid::fromString('97f53c07-4717-4385-9779-89f48c9cebe8')];

        $productCompletenessRemover->deleteForProducts($uuids)->shouldNotBeCalled();
        $legacyCompletenessRemover->deleteForProducts($uuids)->shouldBeCalled();

        $this->deleteForProducts($uuids);
    }
}
