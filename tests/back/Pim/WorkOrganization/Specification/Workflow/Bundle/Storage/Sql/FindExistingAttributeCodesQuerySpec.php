<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql\FindExistingAttributeCodesQuery;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\FindExistingAttributeCodesQuery as QueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FindExistingAttributeCodesQuerySpec extends ObjectBehavior
{
    public function let(Connection $connection): void
    {
        $this->beConstructedWith($connection);
    }

    public function it_is_a_find_existing_attribute_codes_query()
    {
        $this->shouldImplement(QueryInterface::class);
        $this->shouldHaveType(FindExistingAttributeCodesQuery::class);
    }

    public function it_returns_existing_attribute_codes(
        $connection,
        Result $result
    ) {
        $connection->executeQuery(Argument::cetera())->willReturn($result);
        $result->fetchFirstColumn()->willReturn(['description', 'color', 'sku']);

        $this->execute(['description', 'color', 'sku', 'turbulette'])->shouldReturn(['description', 'color', 'sku']);
    }
}
