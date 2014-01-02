<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Reader;

use Pim\Bundle\ImportExportBundle\Reader\File\YamlReader;

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
            'simple'                   => array(),
            'simple_homogenize'        => array(false, true),
            'simple_homogenize_code'   => array(false, true, 'code'),
            'multiple'                 => array(true),
            'multiple_homogenize'      => array(true, true),
            'multiple_homogenize_code' => array(true, true, 'code'),
        );
    }

    /**
     * @dataProvider getReadData
     */
    public function testRead($multiple = false, $homogenize = false, $codeField = false)
    {
        $reader = $this->createReader($multiple, $homogenize, $codeField);
        if ($multiple) {
            $this->assertEquals($this->getExpectedData($homogenize, $codeField), $reader->read());
            $this->assertNull($reader->read());
        } else {
            $index = 0;
            while ($row = $reader->read()) {
                $this->assertEquals($this->getExpectedData($homogenize, $codeField, $index), $row);
                $index++;
            }
        }
    }

    public function testSuccessiveRead()
    {
        $reader = $this->createReader(true);
        $reader->read();

        $reader->setFilePath(__DIR__ . '/../../fixtures/fixture2.yml');
        $this->assertEquals(array('entity5' => array('key1' => 'value5')), $reader->read());
    }

    protected function createReader($multiple = false, $homogenize = false, $codeField = false)
    {
        $reader = new YamlReader($multiple, $homogenize, $codeField);
        $reader->setFilePath(__DIR__ . '/../../fixtures/fixture.yml');

        return $reader;
    }

    protected function getExpectedData($homogenize = false, $codeField = false, $index = false)
    {
        $expected = array(
            'entity1' => array('key1' => 'value1'),
            'entity2' => array('key1' => 'value2', 'key2' => 'value3'),
            'entity3' => array('key1' => 'value4')
        );
        if ($homogenize) {
            $expected['entity1']['key2'] = null;
            $expected['entity3']['key2'] = null;
        }
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
}
