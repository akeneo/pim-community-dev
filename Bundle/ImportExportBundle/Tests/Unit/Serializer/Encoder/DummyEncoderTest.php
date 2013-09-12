<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Serializer\Encoder;

use Oro\Bundle\ImportExportBundle\Serializer\Encoder\DummyEncoder;

class DummyEncoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DummyEncoder
     */
    protected $encoder;

    protected function setUp()
    {
        $this->encoder = new DummyEncoder();
    }

    public function testEncode()
    {
        $data = array('any_data' => new \stdClass());
        $this->assertSame($data, $this->encoder->encode($data, null));
    }

    public function testDecode()
    {
        $data = array('any_data' => new \stdClass());
        $this->assertSame($data, $this->encoder->decode($data, null));
    }

    public function testSupportsEncoding()
    {
        $this->assertFalse($this->encoder->supportsEncoding('json'));
        $this->assertFalse($this->encoder->supportsEncoding(''));
        $this->assertTrue($this->encoder->supportsEncoding(null));
    }

    public function testSupportsDecoding()
    {
        $this->assertFalse($this->encoder->supportsDecoding('json'));
        $this->assertFalse($this->encoder->supportsDecoding(''));
        $this->assertTrue($this->encoder->supportsDecoding(null));
    }
}
