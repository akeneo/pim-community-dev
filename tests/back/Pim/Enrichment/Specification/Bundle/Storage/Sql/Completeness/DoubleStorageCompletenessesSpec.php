<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness\DoubleStorageCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DoubleStorageCompletenessesSpec extends ObjectBehavior
{
    function let(
        SaveProductCompletenesses $legacySaveProductCompletenesses,
        SaveProductCompletenesses $saveProductCompletenesses,
        Connection $connection,
    ): void {
        $this->beConstructedWith(
            $legacySaveProductCompletenesses,
            $saveProductCompletenesses,
            $connection,
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(DoubleStorageCompletenesses::class);
    }

    function it_saves_on_two_storages_with_new_table_is_present(
        SaveProductCompletenesses $legacySaveProductCompletenesses,
        SaveProductCompletenesses $saveProductCompletenesses,
        Connection $connection,
        Result $result,
    ): void {
        $connection->executeQuery(Argument::type('string'), Argument::type('array'))->willReturn($result);
        $result->rowCount()->willReturn(1);

        $completenessCollection = new ProductCompletenessWithMissingAttributeCodesCollection(
            '25d9c9f8-bfb0-4406-88d9-76af05ee3cde',
            [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 5, ['name', 'description']),
            ]
        );

        $legacySaveProductCompletenesses->saveAll([$completenessCollection])->shouldBeCalled();
        $saveProductCompletenesses->saveAll([$completenessCollection])->shouldBeCalled();

        $this->save($completenessCollection);
    }

    function it_only_saves_legacy_storage_without_new_table_is_present(
        SaveProductCompletenesses $legacySaveProductCompletenesses,
        SaveProductCompletenesses $saveProductCompletenesses,
        Connection $connection,
        Result $result,
    ): void {
        $connection->executeQuery(Argument::type('string'), Argument::type('array'))->willReturn($result);
        $result->rowCount()->willReturn(0);

        $completeness = new ProductCompletenessWithMissingAttributeCodesCollection('25d9c9f8-bfb0-4406-88d9-76af05ee3cde', [
            new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 5, ['name', 'description']),
        ]);
        $legacySaveProductCompletenesses->saveAll([$completeness])->shouldBeCalled();
        $saveProductCompletenesses->saveAll([$completeness])->shouldNotBeCalled();

        $this->save($completeness);
    }
}
