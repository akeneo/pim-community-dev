<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Cursor;

use Doctrine\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class CursorSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder, Query $query, Cursor $cursor)
    {
        $queryBuilder->getQuery()->willReturn($query);
        $query->execute()->willReturn($cursor);

        $this->beConstructedWith($queryBuilder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Cursor\Cursor');
        $this->shouldImplement('Akeneo\Component\StorageUtils\Cursor\CursorInterface');
    }

    function it_is_countable($queryBuilder, $query, $cursor)
    {
        $this->shouldImplement('\Countable');

        $queryBuilder->getQuery()->willReturn($query);
        $query->execute()->willReturn($cursor);
        $cursor->count()->willReturn(13);
        $cursor->getNext()->willReturn(null);

        $this->count()->shouldReturn(13);
    }

    function it_is_iterable($cursor)
    {
        $this->shouldImplement('\Iterator');

        $initialData = [
            new Entity(1),
            new Entity(2),
            new Entity(3),
        ];

        $data = array_merge([], $initialData);

        $cursor->getNext()->will(function () use ($cursor, &$data) {
            $stepData = array_shift($data);
            $cursor->current()->willReturn($stepData);

            return $stepData;
        });
        $cursor->reset()->will(function () use ($cursor, &$data, $initialData) {
            $data = array_merge([], $initialData);
        });

        $cursor->count()->willReturn(3);

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
