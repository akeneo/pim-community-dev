<?php

namespace Pim\Bundle\BaseConnectorBundle\Tests\Unit\Reader;

use Pim\Bundle\BaseConnectorBundle\Reader\CachedReader;

/**
 * Test related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CachedReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testRead()
    {
        $reader = new CachedReader();
        $data = array(
            'row1' => array('key1' => 'value1', 'key2' => 'value2'),
            'row2' => array('key1' => 'value3', 'key2' => 'value4'),
        );

        foreach ($data as $key => $value) {
            $reader->addItem($value, $key);
        }

        $data['row2']['extra'] = 'value5';
        $reader->addItem($data['row2'], 'row2');
        $this->assertEquals($data['row2'], $reader->getItem('row2'));

        $newElement = array('key1' => 'value6');
        $data[] = $newElement;
        $reader->addItem($newElement);

        foreach ($data as $row) {
            $this->assertEquals($row, $reader->read());
        }

        $this->assertNull($reader->read());
    }

    public function testGetConfigurationFields()
    {
        $reader = new CachedReader;
        $this->assertEquals(array(), $reader->getConfigurationFields());
    }
}
