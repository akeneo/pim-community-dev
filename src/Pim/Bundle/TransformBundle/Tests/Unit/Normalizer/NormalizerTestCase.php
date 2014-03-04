<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Normalizer;

/**
 * Test case for normalizer objects
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class NormalizerTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NormalizerInterface
     */
    protected $normalizer;

    /**
     * Format for normalization
     * @var string
     */
    protected $format;

    /**
     * Data provider for testing supportsNormalization method
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return [];
    }

    /**
     * Test related method
     * @param string  $class
     * @param string  $format
     * @param boolean $isSupported
     *
     * @dataProvider getSupportNormalizationData
     */
    public function testSupportNormalization($class, $format, $isSupported)
    {
        $data = $this->getMock($class);

        $this->assertSame($isSupported, $this->normalizer->supportsNormalization($data, $format));
    }

    /**
     * Data provider for testing normalize method
     * @return array
     */
    public static function getNormalizeData()
    {
        return [];
    }

    /**
     * Test normalize method
     * @param array $data
     *
     * @dataProvider getNormalizeData
     */
    public function testNormalize(array $data)
    {
        $entity = $this->createEntity($data);

        $this->assertEquals($data, $this->normalizer->normalize($entity, $this->format));
    }

    /**
     * Create entity to normalize
     * @param array $data
     *
     * @return object
     */
    abstract protected function createEntity(array $data);
}
