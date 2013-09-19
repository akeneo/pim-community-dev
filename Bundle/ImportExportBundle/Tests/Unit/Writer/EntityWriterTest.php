<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Writer;

use Oro\Bundle\ImportExportBundle\Writer\EntityWriter;

class EntityWriterTest extends \PHPUnit_Framework_TestCase
{
    public function testWrite()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->exactly(2))
            ->method('persist');
        $em->expects($this->once())
            ->method('flush');
        $writer = new EntityWriter($em);
        $items = array(new \stdClass(), new \stdClass());
        $writer->write($items);
    }
}
