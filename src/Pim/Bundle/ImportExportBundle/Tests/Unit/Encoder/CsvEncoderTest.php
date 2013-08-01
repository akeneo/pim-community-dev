<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Encoder;

use Pim\Bundle\ImportExportBundle\Encoder\CsvEncoder;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvEncoderTest extends \PHPUnit_Framework_TestCase
{
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

    public static function getUnexpectedValues()
    {
        return array(
            array(null),
            array(false),
            array(true),
            array('foo'),
            array(array('foo')),
            array(array('foo', 'bar' => 'baz')),
            array(1),
        );
    }

    public function testIsAnEncoder()
    {
        $this->assertInstanceOf('Symfony\Component\Serializer\Encoder\EncoderInterface', new CsvEncoder);
    }

    public function testSupportCsv()
    {
        $encode = new CsvEncoder;
        $this->assertTrue($encode->supportsEncoding('csv'));
    }

    /**
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

    public function testEncodeCollectionOfHashes()
    {
        $encoder = new CsvEncoder;

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

    public function testEncodeEmptyArray()
    {
        $encoder = new CsvEncoder;

        $this->assertEquals("\n", $encoder->encode(array(), 'csv'));
    }

    /**
     * @dataProvider getUnexpectedValues
     * @expectedException InvalidArgumentException
     */
    public function testEncodeUnexpectedValue($value)
    {
        $encoder = new CsvEncoder;

        $encoder->encode($value, 'csv');
    }

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
     * @dataProvider getEncodeArrayWithHeader
     */
    public function testEncodeArrayWithHeader($array, $csv)
    {
        $encoder = new CsvEncoder();

        $this->assertEquals($csv, $encoder->encode($array, 'csv', array('withHeader' => true)));
    }

    public static function getEncodeArrayWithoutHeader()
    {
        $entry1 = array('name' => 'foo', 'code' => 'bar');
        $entry2 = array('name' => 'baz', 'code' => 'buz');
        $entry3 = array('name' => 'foo', 'data' => 'buz');

        return array(
            array($entry1,                 "foo;bar\n"),
            array(array($entry1, $entry2), "foo;bar\nbaz;buz\n"),
            array(array($entry2, $entry3), "baz;buz;\nfoo;;buz\n"),
        );
    }

    /**
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
        $encoder = new CsvEncoder;

        $encoder->encode(array('foo' => 'bar'), 'csv', array('heterogeneous' => true));
        $encoder->encode(array('boo' => 'far'), 'csv', array('heterogeneous' => true));
    }

    public function testMultipleEncodeOfHomogeneousData()
    {
        $encoder = new CsvEncoder;

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
