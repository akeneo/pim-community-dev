<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Provider;

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

        $em = $this->getMock('\Doctrine\Common\Persistence\ObjectManager');
        $em->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf($entityClass));

        $em->expects($this->once())
            ->method('flush');

        $extractor = new EventNamesExtractor($em, $entityClass);
        $messages = $extractor->extract(__DIR__.'/../Fixtures/');
        $extractor->dumpToDb();

        $this->assertCount(1, $messages, '->extract() should find 1 translation');
        $this->assertTrue(isset($messages['oro.event.good_happens']), '->extract() should find at leat "oro.event.good_happens" message');
    }
}
