<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DateTimeNormalizer;

class DateTimeNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DateTimeNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->normalizer = new DateTimeNormalizer();
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(array()));
        $this->assertTrue($this->normalizer->supportsNormalization(new \DateTime()));
    }

    public function testSupportsDenormalization()
    {
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), 'stdClass'));
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), 'DateTime'));
        $this->assertTrue($this->normalizer->supportsDenormalization('2013-12-31', 'DateTime'));
    }

    public function testNormalize()
    {
        $date = new \DateTime('2013-12-31 23:59:59+0200');
        $this->assertEquals(
            '2013-12-31T23:59:59+0200',
            $this->normalizer->normalize($date, null)
        );
        $this->assertEquals(
            '2013-12-31T23:59:59+0200',
            $this->normalizer->normalize($date, null, array('format' => \DateTime::ISO8601))
        );
        $this->assertEquals(
            '2013-12-31',
            $this->normalizer->normalize($date, null, array('type' => 'date'))
        );
        $this->assertEquals(
            '2013-12-31T23:59:59+0200',
            $this->normalizer->normalize($date, null, array('type' => 'unknown'))
        );
        $this->assertEquals(
            '23:59:59',
            $this->normalizer->normalize($date, null, array('type' => 'time'))
        );
    }

    public function testDenormalize()
    {
        $this->assertEquals(
            new \DateTime('2013-12-31 23:59:59+0200'),
            $this->normalizer->denormalize('2013-12-31T23:59:59+0200', 'DateTime', null)
        );
        $this->assertEquals(
            new \DateTime('2013-12-31 00:00:00', new \DateTimeZone('UTC')),
            $this->normalizer->denormalize('2013-12-31', 'DateTime', null, array('type' => 'date'))
        );
        $this->assertEquals(
            new \DateTime('1970-01-01 23:59:58', new \DateTimeZone('UTC')),
            $this->normalizer->denormalize('23:59:58', 'DateTime', null, array('type' => 'time'))
        );
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\RuntimeException
     * @expectedExceptionMessage Invalid datetime "qwerty", expected format Y-m-d\TH:i:sO.
     */
    public function testDenormalizeException()
    {
        $this->normalizer->denormalize('qwerty', 'DateTime', null);
    }
}
