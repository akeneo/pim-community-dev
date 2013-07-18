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
        $encoder = new CsvEncoder($delimiter, $enclosure);

        $this->assertEquals($expectedResult, $encoder->encode(array('foo', '"bar"'), 'csv'));
    }

    public function testEncodeCollectionOfArrays()
    {
        $encoder = new CsvEncoder;

        $this->assertEquals("foo;bar\nbaz;buz\n", $encoder->encode(array(
            array('foo', 'bar'),
            array('baz', 'buz'),
        ), 'csv'));
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

    public function testEncodeCollectionOfArraysWithHeader()
    {
        $encoder = new CsvEncoder(';', '"', true);

        $this->assertEquals("name;code\nfoo;bar\nbaz;buz\n", $encoder->encode(array(
            array('name' => 'foo', 'code' => 'bar'),
            array('name' => 'baz', 'code' => 'buz'),
        ), 'csv'));
    }

    public function testEncodeCollectionOfArraysWithoutHeader()
    {
        $encoder = new CsvEncoder(';', '"');

        $this->assertEquals("foo;bar\nbaz;buz\n", $encoder->encode(array(
            array('name' => 'foo', 'code' => 'bar'),
            array('name' => 'baz', 'code' => 'buz'),
        ), 'csv'));
    }

    public function testEncodeArrayWithHeader()
    {
        $encoder = new CsvEncoder(';', '"', true);

        $this->assertEquals("name;code\nfoo;bar\n", $encoder->encode(array(
            'name' => 'foo', 'code' => 'bar'
        ), 'csv'));
    }

    public function testEncodeArrayWithoutHeader()
    {
        $encoder = new CsvEncoder(';', '"');

        $this->assertEquals("foo;bar\n", $encoder->encode(array(
            'name' => 'foo', 'code' => 'bar'
        ), 'csv'));
    }
}

