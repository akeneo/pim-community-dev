<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Item\Support;

use Akeneo\Bundle\BatchBundle\Item\Support\EchoWriter;

/**
 * Tests related to the EchoWriter class
 *
 */
class EchoWriterTest extends \PHPUnit_Framework_TestCase
{
    protected $echoWriter = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->echoWriter = new EchoWriter();
    }

    public function testWrite()
    {
        $items = array('item1', 'item2', 'item3');
        $this->expectOutputString("item1\nitem2\nitem3\n");

        $this->echoWriter->write($items);
    }
}
