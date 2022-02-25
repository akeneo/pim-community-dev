<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Persistence\Cache;

use Akeneo\Pim\Permission\Bundle\Persistence\Cache\LRUCachedIsAttributeEditable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LRUCachedIsAttributeEditableSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(LRUCachedIsAttributeEditable::class);
    }

    function let(Connection $connection)
    {
        $this->beConstructedWith($connection);
    }

    function it_performs_sql_query_only_once_with_same_user(
        Connection $connection,
        Result $result
    ) {
        $connection->executeQuery(Argument::type('string'), ['userId' => 1])->shouldBeCalledOnce()->willReturn($result);
        $result->fetchFirstColumn()->shouldBeCalledOnce()->willReturn(['a_text']);

        $this->forCode('attribute1', 1);
        $this->forCode('attribute2', 1);
    }
}
