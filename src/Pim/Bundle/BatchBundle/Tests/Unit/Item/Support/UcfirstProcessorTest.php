<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Item\Support;

use Pim\Bundle\BatchBundle\Item\Support\UcfirstProcessor;

/**
 * Tests related to the UcfirstProcessor class
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class UcfirstProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected $ucfirstProcessor = null;

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
