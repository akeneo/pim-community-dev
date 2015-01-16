<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Cursor;

class MongoDBODMCursorSpec extends ObjectBehavior
{
    public function let(
        Builder $queryBuilder
    )
    {
        $this->beConstructedWith($queryBuilder);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Cursor');
        $this->shouldImplement('Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorInterface');
    }

    public function it_is_countable($queryBuilder, Query $query, Cursor $cursor)
    {
        $this->shouldImplement('\Countable');

        $queryBuilder->getQuery()->shouldBeCalled()->willReturn($query);
        $query->execute()->shouldBeCalled()->willReturn($cursor);
        $cursor->count()->shouldBeCalled()->willReturn(13);
        $cursor->getNext()->shouldBeCalled()->willReturn(null);

        $this->shouldHaveCount(13);
    }

    public function it_is_iterable($queryBuilder, Query $query, Cursor $cursor, Entity $entity)
    {
        $this->shouldImplement('\Iterator');

        $queryBuilder->getQuery()->shouldBeCalled()->willReturn($query);
        $query->execute()->shouldBeCalled()->willReturn($cursor);
        $cursor->count()->shouldBeCalled()->willReturn(13);
        $cursor->getNext()->shouldBeCalled()->willReturn(null);
        $cursor->rewind()->shouldBeCalled()->willReturn(null);
        $cursor->current()->shouldBeCalled()->willReturn($entity);

        $this->rewind()->shouldReturn(null);
        $this->valid()->shouldReturn(true);
        $this->current()->shouldReturn($entity);
        $this->key()->shouldReturn(0);
        $this->next()->shouldReturn(null);
    }
}

class Entity{

}

