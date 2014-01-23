<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Pim\Bundle\ImportExportBundle\Processor\DummyProcessor;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DummyProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $processor = new DummyProcessor;
        $this->assertNull($processor->process([]));
    }

    public function testGetConfigurationFields()
    {
        $processor = new DummyProcessor;
        $this->assertEquals([], $processor->getConfigurationFields());
    }
}
