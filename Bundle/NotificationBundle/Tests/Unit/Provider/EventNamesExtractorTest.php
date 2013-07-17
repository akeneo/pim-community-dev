<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Provider;

use Oro\Bundle\NotificationBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Oro\Bundle\NotificationBundle\Provider\EventNamesExtractor;

class EventNamesExtractorTest extends TestCase
{
    /**
     * Test extraction works OK
     */
    public function testExtract()
    {
        $entityClass = 'Oro\Bundle\NotificationBundle\Entity\Event';

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue(new Event('test.test')));

        $em = $this->getMock('\Doctrine\Common\Persistence\ObjectManager');
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($entityClass))
            ->will($this->returnValue($repository));

        $em->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf($entityClass));

        $em->expects($this->once())
            ->method('flush');

        $extractor = new EventNamesExtractor($em, $entityClass);
        $messages = $extractor->extract(__DIR__ . '/../Fixtures/', false);
        $extractor->dumpToDb();

        $this->assertCount(1, $messages, '->extract() should find 1 translation');
        $this->assertTrue(isset($messages['oro.event.good_happens_unittest']), '->extract() should find at leat "oro.event.good_happens" message');
    }

    public function testDumpToDb()
    {
        $entityClass = 'Oro\Bundle\NotificationBundle\Entity\Event';
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue(array(new Event('test.test'))));

        $em = $this->getMock('\Doctrine\Common\Persistence\ObjectManager');
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($entityClass))
            ->will($this->returnValue($repository));


        $em->expects($this->once())
            ->method('flush');

        $extractor = new EventNamesExtractor($em, $entityClass);
        $extractor->setEventNames(array('test.test' => 'test.test'));
        $extractor->dumpToDb();
    }
}
