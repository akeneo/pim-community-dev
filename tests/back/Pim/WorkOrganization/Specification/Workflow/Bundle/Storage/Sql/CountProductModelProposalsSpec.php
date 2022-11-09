<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\CountProductModelProposals;
use Prophecy\Argument;

class CountProductModelProposalsSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection);
    }

    function it_is_a_count_product_proposals_query()
    {
        $this->shouldImplement(CountProductModelProposals::class);
    }

    function it_counts_the_total_number_of_product_model_proposals($connection, Statement $statement)
    {
        $connection->query(Argument::type('string'))->willReturn($statement);
        $statement->fetchColumn(0)->willReturn('42');

        $this->fetch()->shouldReturn(42);
    }
}
