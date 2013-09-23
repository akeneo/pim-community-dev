<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Manager\DTO;

use Oro\Bundle\ImapBundle\Manager\DTO\ItemId;

class ItemIdTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $obj = new ItemId(10, 20);
        $this->assertEquals(10, $obj->getUid());
        $this->assertEquals(20, $obj->getUidValidity());
    }

    public function testGettersAndSetters()
    {
        $obj = new ItemId(1, 2);
        $obj
            ->setUid(10)
            ->setUidValidity(20);
        $this->assertEquals(10, $obj->getUid());
        $this->assertEquals(20, $obj->getUidValidity());
    }
}
