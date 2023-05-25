<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\SwitchSqlGetCompletenessRatio;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetProductCompletenessRatio;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SwitchSqlGetCompletenessRatioSpec extends ObjectBehavior
{
    function let(
        GetProductCompletenessRatio $legacyGetProductCompletenessRatio,
        GetProductCompletenessRatio $getProductCompletenessRatio,
        Connection $connection,
    ) {
        $this->beConstructedWith(
            $legacyGetProductCompletenessRatio,
            $getProductCompletenessRatio,
            $connection,
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SwitchSqlGetCompletenessRatio::class);
    }

    function it_get_ratio_on_legacy_table_with_new_table_is_present(
        GetProductCompletenessRatio $legacyGetProductCompletenessRatio,
        GetProductCompletenessRatio $getProductCompletenessRatio,
        Connection $connection,
        Result $result,
    ): void
    {
        $connection->executeQuery(Argument::type('string'), Argument::type('array'))->willReturn($result);
        $result->rowCount()->willReturn(1);

        $uuid = Uuid::fromString('97f53c07-4717-4385-9779-89f48c9cebe8');
        $getProductCompletenessRatio->forChannelCodeAndLocaleCode($uuid,'ecommerce', 'en_US')->willReturn(3);

        $getProductCompletenessRatio->forChannelCodeAndLocaleCode($uuid,'ecommerce', 'en_US')->shouldBeCalled();
        $legacyGetProductCompletenessRatio->forChannelCodeAndLocaleCode($uuid,'ecommerce', 'en_US')->shouldNotBeCalled();

        $this->forChannelCodeAndLocaleCode($uuid,'ecommerce', 'en_US');
    }

    function it_get_ratio_on_legacy_table_with_new_table_is_not_present(
        GetProductCompletenessRatio $legacyGetProductCompletenessRatio,
        GetProductCompletenessRatio $getProductCompletenessRatio,
        Connection $connection,
        Result $result,
    ): void
    {
        $connection->executeQuery(Argument::type('string'), Argument::type('array'))->willReturn($result);
        $result->rowCount()->willReturn(0);

        $uuid = Uuid::fromString('97f53c07-4717-4385-9779-89f48c9cebe8');
        $getProductCompletenessRatio->forChannelCodeAndLocaleCode($uuid,'ecommerce', 'en_US')->willReturn(3);

        $getProductCompletenessRatio->forChannelCodeAndLocaleCode($uuid,'ecommerce', 'en_US')->shouldNotBeCalled();
        $legacyGetProductCompletenessRatio->forChannelCodeAndLocaleCode($uuid,'ecommerce', 'en_US')->shouldBeCalled();

        $this->forChannelCodeAndLocaleCode($uuid,'ecommerce', 'en_US');
    }
}
