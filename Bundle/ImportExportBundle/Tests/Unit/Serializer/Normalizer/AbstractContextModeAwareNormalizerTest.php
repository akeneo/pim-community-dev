<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\AbstractContextModeAwareNormalizer;

class AbstractContextModeAwareNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractContextModeAwareNormalizer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->normalizer = $this
            ->getMockBuilder('Oro\Bundle\ImportExportBundle\Serializer\Normalizer\AbstractContextModeAwareNormalizer')
            ->setConstructorArgs(array(array('import', 'export'), 'import'))
            ->getMockForAbstractClass();
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\RuntimeException
     * @expectedExceptionMessage Normalization with mode "import" is not supported
     */
    public function testNormalizeException()
    {
        $this->normalizer->normalize(new \stdClass());
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\RuntimeException
     * @expectedExceptionMessage Mode "unknown" is not supported
     */
    public function testNormalizeUnsupportedMode()
    {
        $this->normalizer->normalize(new \stdClass(), null, array('mode' => 'unknown'));
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\RuntimeException
     * @expectedExceptionMessage Mode "unknown" is not supported, available modes are "import", export"
     */
    public function testConstructorException()
    {
        $this->normalizer = $this
            ->getMockBuilder('Oro\Bundle\ImportExportBundle\Serializer\Normalizer\AbstractContextModeAwareNormalizer')
            ->setConstructorArgs(array(array('import', 'export'), 'unknown'))
            ->getMockForAbstractClass();
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\RuntimeException
     * @expectedExceptionMessage Denormalization with mode "import" is not supported
     */
    public function testDenormalizeUnsupportedMode()
    {
        $this->normalizer->denormalize('test', '\stdClass');
    }
}
