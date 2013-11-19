<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

/**
 * Test case for normalizer objects
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @abstract
 */
abstract class NormalizerTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NormalizerInterface
     */
    protected $normalizer;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = $this->createNormalizer();
    }

    /**
     * Create normalizer
     * @return NormalizerInterface
     * @abstract
     */
    abstract protected function createNormalizer();

    /**
     * Get entity class name
     * @return string
     * @abstract
     * @static
     */
    abstract protected static function getEntityClassName();

    /**
     * Data provider for testing supportsNormalization method
     * @return array
     * @static
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array(static::getEntityClassName(), 'json', true),
            array(static::getEntityClassName(), 'xml', true),
            array(static::getEntityClassName(), 'csv', false),
            array('stdClass', 'json', false),
            array('stdClass', 'json', false)
        );
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
     * @abstract
     * @static
     */
    abstract public static function getNormalizeData();

    /**
     * Test normalize method
     * @param array $data
     *
     * @dataProvider getNormalizeData
     */
    public function testNormalize(array $data)
    {
        $entity = $this->createEntity($data);

        $this->assertEquals($data, $this->normalizer->normalize($entity, 'csv'));
    }

    /**
     * Create entity to normalize
     * @param array
     * @return object
     * @abstract
     */
    abstract protected function createEntity(array $data);
}
