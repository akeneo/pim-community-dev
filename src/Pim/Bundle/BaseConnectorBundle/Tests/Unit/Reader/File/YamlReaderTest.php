<?php

namespace Pim\Bundle\BaseConnectorBundle\Tests\Unit\Reader\File;

use Pim\Bundle\BaseConnectorBundle\Reader\File\YamlReader;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class YamlReaderTest extends \PHPUnit_Framework_TestCase
{
    public function getReadData()
    {
        return array(
            'simple'        => array(),
            'simple_code'   => array(false, 'code'),
            'multiple'      => array(true),
            'multiple_code' => array(true, true, 'code'),
        );
    }

    /**
     * @dataProvider getReadData
     */
    public function testRead($multiple = false, $codeField = false)
    {
        $reader = $this->createReader($multiple, $codeField);
        if ($multiple) {
            $this->assertEquals($this->getExpectedData($codeField), $reader->read());
            $this->assertNull($reader->read());
        } else {
            $index = 0;
            while ($row = $reader->read()) {
                $this->assertEquals($this->getExpectedData($codeField, $index), $row);
                $index++;
            }
        }
    }

    public function testSuccessiveRead()
    {
        $reader = $this->createReader(true);
        $reader->read();

        $reader->setFilePath(__DIR__ . '/../../../fixtures/fixture2.yml');
        $this->assertEquals(array('entity5' => array('key1' => 'value5')), $reader->read());
    }

    protected function createReader($multiple = false, $codeField = false)
    {
        $reader = new YamlReader($multiple, $codeField);
        $reader->setFilePath(__DIR__ . '/../../../fixtures/fixture.yml');

        return $reader;
    }

    protected function getExpectedData($codeField = false, $index = false)
    {
        $expected = array(
            'entity1' => array('key1' => 'value1'),
            'entity2' => array('key1' => 'value2', 'key2' => 'value3'),
            'entity3' => array('key1' => 'value4')
        );
        if ($codeField) {
            foreach (array_keys($expected) as $code) {
                $expected[$code][$codeField] = $code;
            }
        }

        if (false === $index) {
            return $expected;
        } else {
            $expected = array_values($expected);

            return $expected[$index];
        }
    }

    public function testGetConfigurationFields()
    {
        $reader = new YamlReader;
        $this->assertEquals(array(), $reader->getConfigurationFields());
    }
}
