<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Item\Support;

use Akeneo\Bundle\BatchBundle\Item\Support\UcfirstProcessor;

/**
 * Tests related to the UcfirstProcessor class
 *
 */
class UcfirstProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected $ucfirstProcessor = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->ucfirstProcessor = new UcfirstProcessor();
    }

    public function testProcess()
    {
        $item = "my_item";
        $expectedResult = "My_item";
        $this->assertEquals($expectedResult, $this->ucfirstProcessor->process($item));
    }
}
