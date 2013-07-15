<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Provider;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Oro\Bundle\NotificationBundle\Provider\EventNamesExtractor;

class EventNamesExtractorTest extends TestCase
{
    public function testExtraction()
    {
        $bundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $bundle->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('OroAbcBundle'));

        $bundles = array(
            $bundle
        );

        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $kernel->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue($bundles));

        $extractor = new EventNamesExtractor($kernel);
        $messages = $extractor->extract(__DIR__.'/../Fixtures/Resources/views/');

        // Assert
        $this->assertCount(1, $messages, '->extract() should find 1 translation');
        $this->assertTrue(isset($messages['oro.event.good_happens']), '->extract() should find at leat "oro.event.good_happens" message');
    }
}
