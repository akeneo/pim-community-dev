<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Writer;

use Oro\Bundle\ImportExportBundle\Writer\DoctrineClearWriter;

class DoctrineClearWriterTest extends \PHPUnit_Framework_TestCase
{
    public function testWrite()
    {
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('clear');
        $writer = new DoctrineClearWriter($entityManager);
        $writer->write(array());
    }
}
