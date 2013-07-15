<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Provider;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Translation\MessageCatalogue;
use Oro\Bundle\NotificationBundle\Provider\EventTranslationExtractor;

class EventTranslationExtractorTest extends TestCase
{
    public function testExtraction()
    {
        $eventNamesExtractor = $this->getMockBuilder('Oro\Bundle\NotificationBundle\Provider\EventNamesExtractor')
            ->disableOriginalConstructor()
            ->getMock();

        $dir = 'someDir';
        $testMessage = 'test message';
        $eventNamesExtractor->expects($this->once())
            ->method('extract')
            ->with($dir.'/../../')
            ->will($this->returnValue(array(
                $testMessage => $testMessage,
            )));

        $extractor = new EventTranslationExtractor($eventNamesExtractor);
        $extractor->setPrefix('prefix');
        $catalogue = new MessageCatalogue('en');

        $extractor->extract($dir, $catalogue);

        // Assert
        $this->assertCount(1, $catalogue->all('messages'), '->extract() should find 1 translation');
        $this->assertTrue($catalogue->has($testMessage), '->extract() should find at leat "' . $testMessage . '" message');
        $this->assertEquals('prefix'.$testMessage, $catalogue->get($testMessage), '->extract() should apply "prefix" as prefix');
    }
}
