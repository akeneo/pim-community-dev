<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Item\Support;

use Pim\Bundle\BatchBundle\Item\Support\NoopProcessor;

/**
 * Tests related to the NoopProcessor class
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class NoopProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected $noopProcessor = null;

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
