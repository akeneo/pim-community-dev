<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Item\Support;

use Akeneo\Bundle\BatchBundle\Item\Support\NoopProcessor;

/**
 * Tests related to the NoopProcessor class
 *
 */
class NoopProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected $noopProcessor = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->noopProcessor = new NoopProcessor();
    }

    public function testProcess()
    {
        $item = "my_item";
        $this->assertEquals($item, $this->noopProcessor->process($item));
    }
}
