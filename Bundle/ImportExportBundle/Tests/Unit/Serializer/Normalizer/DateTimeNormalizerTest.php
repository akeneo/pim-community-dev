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
    }

    public function testDenormalize()
    {
        $date = new \DateTime('2013-12-31 23:59:59+0200');
        $this->assertEquals(
            $date,
            $this->normalizer->denormalize('2013-12-31T23:59:59+0200', null)
        );
    }
}
