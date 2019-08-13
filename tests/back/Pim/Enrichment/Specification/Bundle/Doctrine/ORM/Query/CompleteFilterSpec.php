<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\CompleteFilter;
use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CompleteFilterInterface;

class CompleteFilterSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompleteFilter::class);
    }

    function it_it_is_a_query()
    {
        $this->shouldImplement(CompleteFilterInterface::class);
    }
}
