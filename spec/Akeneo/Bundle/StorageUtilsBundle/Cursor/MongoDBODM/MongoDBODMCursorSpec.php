<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Cursor\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Doctrine\Bundle\MongoDBBundle\Cursor;
use Doctrine\ODM\MongoDB\Query\Builder;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\AbstractCursor;

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
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Cursor\MongoDBODM\MongoDBODMCursor');
    }

    public function it_is_a_rule_applier()
    {
        $this->shouldHaveType('Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorInterface');
    }

}
