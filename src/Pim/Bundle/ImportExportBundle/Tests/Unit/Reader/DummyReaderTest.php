<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Reader;

use Pim\Bundle\ImportExportBundle\Reader\DummyReader;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DummyReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testRead()
    {
        $reader = new DummyReader;
        $this->assertNull($reader->read());
    }

    public function testGetConfigurationFields()
    {
        $reader = new DummyReader;
        $this->assertEquals(array(), $reader->getConfigurationFields());
    }
}
