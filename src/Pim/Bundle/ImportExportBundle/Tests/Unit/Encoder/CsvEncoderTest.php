<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Encoder;

use Pim\Bundle\ImportExportBundle\Encoder\CsvEncoder;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvEncoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public static function getEncodeSimpleArrayData()
    {
        return array(
            array(',',  '"',  "foo,\"\"\"bar\"\"\"\n"),
            array(null, null, "foo;\"\"\"bar\"\"\"\n"),
            array(';',  '"',  "foo;\"\"\"bar\"\"\"\n"),
            array(';',  '\'', "foo;\"bar\"\n"),
            array(null, '\'', "foo;\"bar\"\n"),
            array(',',  null, "foo,\"\"\"bar\"\"\"\n"),
        );
    }

    /**
     * @return array
     */
    public static function getUnexpectedValues()
    {
        return array(
            array(null),
            array(false),
            array(true),
            array('foo'),
            array(1),
        );
    }

    /**
     * Test related method
     */
    public function testIsAnEncoder()
    {
        $this->assertInstanceOf('Symfony\Component\Serializer\Encoder\EncoderInterface', new CsvEncoder());
    }

    /**
     * Test related method
     */
    public function testSupportCsv()
    {
        $encode = new CsvEncoder();
        $this->assertTrue($encode->supportsEncoding('csv'));
    }

    /**
     * @param string $delimiter
     * @param string $enclosure
     * @param string $expectedResult
     *
     * @dataProvider getEncodeSimpleArrayData
     */
    public function testEncodeArray($delimiter, $enclosure, $expectedResult)
    {
        $encoder = new CsvEncoder();

        $this->assertEquals(
            $expectedResult,
            $encoder->encode(
                array(
                    'code' => 'foo',
                    'name' => '"bar"'
                ),
                'csv',
                array(
                    'delimiter' => $delimiter,
                    'enclosure' => $enclosure,
                )
            )
        );
    }

    /**
     * Test related method
     */
    public function testEncodeCollectionOfHashes()
    {
        $encoder = new CsvEncoder();

        $this->assertEquals(
            "foo;bar\nbaz;buz\n",
            $encoder->encode(
                array(
                    array('name' => 'foo', 'code' => 'bar'),
                    array('name' => 'baz', 'code' => 'buz'),
                ),
                'csv'
            )
        );
    }

    /**
     * Test related method
     */
    public function testEncodeEmptyArray()
    {
        $encoder = new CsvEncoder();

        $this->assertEquals("\n", $encoder->encode(array(), 'csv'));
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getUnexpectedValues
     * @expectedException InvalidArgumentException
     */
    public function testEncodeUnexpectedValue($value)
    {
        $encoder = new CsvEncoder();

        $encoder->encode($value, 'csv');
    }

    /**
     * @return array
     */
    public static function getEncodeArrayWithHeader()
    {
        $entry1 = array('name' => 'foo', 'code' => 'bar');
        $entry2 = array('name' => 'baz', 'code' => 'buz');
        $entry3 = array('name' => 'foo', 'data' => 'buz');

        return array(
            array($entry1,                 "name;code\nfoo;bar\n"),
            array(array($entry1, $entry2), "name;code\nfoo;bar\nbaz;buz\n"),
            array(array($entry2, $entry3), "name;code;data\nbaz;buz;\nfoo;;buz\n"),
        );
    }

    /**
     * @param array  $array
     * @param string $csv
     *
     * @dataProvider getEncodeArrayWithHeader
     */
    public function testEncodeArrayWithHeader($array, $csv)
    {
        $encoder = new CsvEncoder();

        $this->assertEquals($csv, $encoder->encode($array, 'csv', array('withHeader' => true)));
    }

    /**
     * @return array
     */
    public static function getEncodeArrayWithoutHeader()
    {
        $entry1 = array('name' => 'foo', 'code' => 'bar');
        $entry2 = array('name' => 'baz', 'code' => 'buz');
        $entry3 = array('name' => 'foo', 'data' => 'buz');
        $entry4 = array('foo');
        $entry5 = array('foo', 'bar' => 'baz');

        return array(
            array($entry1,                 "foo;bar\n"),
            array(array($entry1, $entry2), "foo;bar\nbaz;buz\n"),
            array(array($entry2, $entry3), "baz;buz;\nfoo;;buz\n"),
            array($entry4,                 "foo\n"),
            array($entry5,                 "foo;baz\n"),
        );
    }

    /**
     * @param array  $array
     * @param string $csv
     *
     * @dataProvider getEncodeArrayWithoutHeader
     */
    public function testEncodeArrayWithoutHeader($array, $csv)
    {
        $encoder = new CsvEncoder();

        $this->assertEquals($csv, $encoder->encode($array, 'csv'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testMultipleEncodeOfHeterogeneousData()
    {
        $encoder = new CsvEncoder();

        $encoder->encode(array('foo' => 'bar'), 'csv', array('heterogeneous' => true));
        $encoder->encode(array('boo' => 'far'), 'csv', array('heterogeneous' => true));
    }

    /**
     * Test related method
     */
    public function testMultipleEncodeOfHomogeneousData()
    {
        $encoder = new CsvEncoder();

        $this->assertEquals(
            "foo\nbar\n",
            $encoder->encode(array('foo' => 'bar'), 'csv', array('withHeader' => true, 'heterogeneous' => false))
        );
        $this->assertEquals(
            "baz\n",
            $encoder->encode(array('foo' => 'baz'), 'csv', array('withHeader' => true, 'heterogeneous' => false))
        );
    }
}
