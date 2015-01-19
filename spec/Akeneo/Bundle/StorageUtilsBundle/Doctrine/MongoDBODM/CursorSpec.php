<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Cursor;

class CursorSpec extends ObjectBehavior
{
    public function let(
        Builder $queryBuilder
    ) {
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

        $initialData = [
            new Entity(1),
            new Entity(2),
            new Entity(3),
        ];

        $data = array_merge([], $initialData);

        $queryBuilder->getQuery()->shouldBeCalled()->willReturn($query);
        $query->execute()->shouldBeCalled()->willReturn($cursor);

        $cursor->current()->shouldBeCalled()->willReturn($entity);

        $cursor->getNext()->shouldBeCalled()->will(function () use ($cursor, &$data) {
            $cursor->current()->willReturn(array_shift($data));
        });
        $cursor->rewind()->shouldBeCalled()->will(function () use ($cursor, &$data, $initialData) {
            $data = array_merge([], $initialData);
        });

        $cursor->count()->shouldBeCalled()->willReturn(3);

        // methods that not iterate can be called twice
        $this->rewind()->shouldReturn(null);
        $this->valid()->shouldReturn(true);
        $this->valid()->shouldReturn(true);
        $this->current()->shouldReturn($initialData[0]);
        $this->current()->shouldReturn($initialData[0]);
        $this->key()->shouldReturn(0);
        $this->key()->shouldReturn(0);

        // for each call sequence
        $this->rewind()->shouldReturn(null);
        $this->valid()->shouldReturn(true);
        $this->current()->shouldReturn($initialData[0]);
        $this->key()->shouldReturn(0);

        $this->next()->shouldReturn(null);
        $this->valid()->shouldReturn(true);
        $this->current()->shouldReturn($initialData[1]);
        $this->key()->shouldReturn(1);

        $this->next()->shouldReturn(null);
        $this->valid()->shouldReturn(true);
        $this->current()->shouldReturn($initialData[2]);
        $this->key()->shouldReturn(2);

        $this->next()->shouldReturn(null);
        $this->valid()->shouldReturn(false);

        // check behaviour after the end of data
        $this->current()->shouldReturn(false);
        $this->key()->shouldReturn(null);
    }
}

class Entity
{

    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

}

