<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Writer;

use Oro\Bundle\ImportExportBundle\Writer\EntityWriter;
use Oro\Bundle\ImportExportBundle\Writer\EntityDetachFixer;

class EntityWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $detachFixer;

    /**
     * @var EntityWriter
     */
    protected $writer;

    protected function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->detachFixer = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Writer\EntityDetachFixer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->writer = new EntityWriter($this->entityManager, $this->detachFixer);
    }

    public function testWrite()
    {
        $fooItem = $this->getMock('FooItem');
        $barItem = $this->getMock('BarItem');

        $this->detachFixer->expects($this->at(0))
            ->method('fixEntityAssociationFields')
            ->with($fooItem, 1);

        $this->detachFixer->expects($this->at(1))
            ->method('fixEntityAssociationFields')
            ->with($barItem, 1);

        $this->entityManager->expects($this->at(0))
            ->method('persist')
            ->with($fooItem);

        $this->entityManager->expects($this->at(1))
            ->method('persist')
            ->with($barItem);

        $this->entityManager->expects($this->at(2))
            ->method('flush');

        $items = array($fooItem, $barItem);
        $this->writer->write($items);
    }
}
