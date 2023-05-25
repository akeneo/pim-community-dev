<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness\DoubleSqlGetCompleteness;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness\SwitchSqlGetCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SwitchSqlGetCompletenessSpec extends ObjectBehavior
{
    function let(
        GetProductCompletenesses $legacyGetProductCompletenesses,
        GetProductCompletenesses $getProductCompletenesses,
        Connection $connection,
        Result $result
    ) {
        $connection->executeQuery(Argument::cetera())->willReturn($result);

        $this->beConstructedWith(
            $legacyGetProductCompletenesses,
            $getProductCompletenesses,
            $connection,
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SwitchSqlGetCompleteness::class);
    }

    function it_gets_completenesses_from_new_table(
        GetProductCompletenesses $legacyGetProductCompletenesses,
        GetProductCompletenesses $getProductCompletenesses,
        Result $result,
    ): void {
        $this->newTableExists($result, true);
        $uuid = Uuid::fromString('97f53c07-4717-4385-9779-89f48c9cebe8');

        $completenesses = [
            '97f53c07-4717-4385-9779-89f48c9cebe8' => new ProductCompletenessCollection(
                $uuid,
                [
                    new ProductCompleteness('ecommerce', 'en_US', 4, 2),
                ]
            )
        ];
        $getProductCompletenesses->fromProductUuids([$uuid], null, [])->shouldBeCalled()->willReturn($completenesses);
        $legacyGetProductCompletenesses->fromProductUuids(Argument::cetera())->shouldNotBeCalled();

        $this->fromProductUuids([$uuid])->shouldReturn($completenesses);
    }

    function it_gets_completenesses_from_legacy_table_if_new_table_does_not_exist(
        GetProductCompletenesses $legacyGetProductCompletenesses,
        GetProductCompletenesses $getProductCompletenesses,
        Result $result,
    ): void {
        $this->newTableExists($result, false);

        $uuid = Uuid::fromString('97f53c07-4717-4385-9779-89f48c9cebe8');

        $completenesses = [
            '97f53c07-4717-4385-9779-89f48c9cebe8' => new ProductCompletenessCollection(
                $uuid,
                [
                    new ProductCompleteness('ecommerce', 'en_US', 4, 2),
                ]
            )
        ];
        $getProductCompletenesses->fromProductUuids(Argument::cetera())->shouldNotBeCalled();
        $legacyGetProductCompletenesses->fromProductUuids([$uuid], null, [])->shouldBeCalled()->willReturn($completenesses);

        $this->fromProductUuids([$uuid])->shouldReturn($completenesses);
    }

    function it_gets_completenesses_from_legacy_table_if_product_is_not_in_the_new_table(
        GetProductCompletenesses $legacyGetProductCompletenesses,
        GetProductCompletenesses $getProductCompletenesses,
        Result $result,
    ): void {
        $this->newTableExists($result, true);

        $uuids = [
            Uuid::fromString('97f53c07-4717-4385-9779-89f48c9cebe8'),
            Uuid::fromString('0836adb2-06fb-46d4-a04e-00c45818b5ab'),
        ];

        $getProductCompletenesses->fromProductUuids($uuids, null, [])->shouldBeCalled()->willReturn([
            '97f53c07-4717-4385-9779-89f48c9cebe8' => new ProductCompletenessCollection(Uuid::fromString('97f53c07-4717-4385-9779-89f48c9cebe8'), [
                new ProductCompleteness('ecommmerce', 'en_US', 4, 3),
            ]),
            '0836adb2-06fb-46d4-a04e-00c45818b5ab' => new ProductCompletenessCollection(Uuid::fromString('0836adb2-06fb-46d4-a04e-00c45818b5ab'), []),
        ]);
        $legacyGetProductCompletenesses->fromProductUuids(
            // check that the legacy query is only called with the UUID with empty completeness
            Argument::that(function($uuids) {
                if (!\is_array($uuids) || count($uuids) !== 1) {
                    return false;
                }
                $uuid = array_values($uuids)[0];

                return $uuid instanceof UuidInterface && $uuid->toString() === '0836adb2-06fb-46d4-a04e-00c45818b5ab';
            }), null, [])
            ->shouldBeCalled()
            ->willReturn([
                '0836adb2-06fb-46d4-a04e-00c45818b5ab' => new ProductCompletenessCollection(Uuid::fromString('0836adb2-06fb-46d4-a04e-00c45818b5ab'), [
                    new ProductCompleteness('ecommmerce', 'en_US', 4, 4),
                ])
            ])
        ;

        $this->fromProductUuids($uuids, null, [])->shouldBeLike(
            [
                '97f53c07-4717-4385-9779-89f48c9cebe8' => new ProductCompletenessCollection(Uuid::fromString('97f53c07-4717-4385-9779-89f48c9cebe8'), [
                    new ProductCompleteness('ecommmerce', 'en_US', 4, 3),
                ]),
                '0836adb2-06fb-46d4-a04e-00c45818b5ab' => new ProductCompletenessCollection(Uuid::fromString('0836adb2-06fb-46d4-a04e-00c45818b5ab'), [
                    new ProductCompleteness('ecommmerce', 'en_US', 4, 4),
                ]),
            ]
        );
    }

    private function newTableExists(Result $result, bool $exists)
    {
        $result->rowCount()->willReturn($exists ? 1 : 0);
    }
}
