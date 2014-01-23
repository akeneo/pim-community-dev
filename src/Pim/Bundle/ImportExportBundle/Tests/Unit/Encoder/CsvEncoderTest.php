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
        return [
            [',',  '"',  "foo,\"\"\"bar\"\"\"\n"],
            [null, null, "foo;\"\"\"bar\"\"\"\n"],
            [';',  '"',  "foo;\"\"\"bar\"\"\"\n"],
            [';',  '\'', "foo;\"bar\"\n"],
            [null, '\'', "foo;\"bar\"\n"],
            [',',  null, "foo,\"\"\"bar\"\"\"\n"],
        ];
    }

    /**
     * @return array
     */
    public static function getUnexpectedValues()
    {
        return [
            [null],
            [false],
            [true],
            ['foo'],
            [1],
        ];
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
                [
                    'code' => 'foo',
                    'name' => '"bar"'
                ],
                'csv',
                [
                    'delimiter' => $delimiter,
                    'enclosure' => $enclosure,
                ]
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
                [
                    ['name' => 'foo', 'code' => 'bar'],
                    ['name' => 'baz', 'code' => 'buz'],
                ],
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

        $this->assertEquals("\n", $encoder->encode([], 'csv'));
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
        $entry1 = ['name' => 'foo', 'code' => 'bar'];
        $entry2 = ['name' => 'baz', 'code' => 'buz'];
        $entry3 = ['name' => 'foo', 'data' => 'buz'];

        return [
            [$entry1,                 "name;code\nfoo;bar\n"],
            [[$entry1, $entry2], "name;code\nfoo;bar\nbaz;buz\n"],
            [[$entry2, $entry3], "name;code;data\nbaz;buz;\nfoo;;buz\n"],
        ];
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

        $this->assertEquals($csv, $encoder->encode($array, 'csv', ['withHeader' => true]));
    }

    /**
     * @return array
     */
    public static function getEncodeArrayWithoutHeader()
    {
        $entry1 = ['name' => 'foo', 'code' => 'bar'];
        $entry2 = ['name' => 'baz', 'code' => 'buz'];
        $entry3 = ['name' => 'foo', 'data' => 'buz'];
        $entry4 = ['foo'];
        $entry5 = ['foo', 'bar' => 'baz'];

        return [
            [$entry1,                 "foo;bar\n"],
            [[$entry1, $entry2], "foo;bar\nbaz;buz\n"],
            [[$entry2, $entry3], "baz;buz;\nfoo;;buz\n"],
            [$entry4,                 "foo\n"],
            [$entry5,                 "foo;baz\n"],
        ];
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

        $encoder->encode(['foo' => 'bar'], 'csv', ['heterogeneous' => true]);
        $encoder->encode(['boo' => 'far'], 'csv', ['heterogeneous' => true]);
    }

    /**
     * Test related method
     */
    public function testMultipleEncodeOfHomogeneousData()
    {
        $encoder = new CsvEncoder();

        $this->assertEquals(
            "foo\nbar\n",
            $encoder->encode(['foo' => 'bar'], 'csv', ['withHeader' => true, 'heterogeneous' => false])
        );
        $this->assertEquals(
            "baz\n",
            $encoder->encode(['foo' => 'baz'], 'csv', ['withHeader' => true, 'heterogeneous' => false])
        );
    }
}
