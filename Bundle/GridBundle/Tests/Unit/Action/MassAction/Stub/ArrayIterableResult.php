<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction\Stub;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\GridBundle\Datagrid\IterableResultInterface;

class ArrayIterableResult extends \ArrayIterator implements IterableResultInterface
{
    public function setBufferSize($size)
    {
    }

    public function getSource()
    {
    }
}
