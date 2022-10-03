<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\CountProductProposals;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CountProductProposalsSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection);
    }

    function it_is_a_count_product_proposals_query()
    {
        $this->shouldImplement(CountProductProposals::class);
    }

    function it_counts_the_total_number_of_product_proposals($connection, Result $result)
    {
        $connection->fetchOne(Argument::type('string'))->willReturn('42');

        $this->fetch()->shouldReturn(42);
    }
}
