<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ChannelNormalizer
     */
    protected $normalizer;

    /**
     * @var string
     */
    protected $format;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new ChannelNormalizer();
        $this->format     = 'json';
    }

    /**
     * Data provider for testing supportsNormalization method
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\Channel', 'json', true),
            array('Pim\Bundle\CatalogBundle\Entity\Channel', 'csv', false),
            array('stdClass', 'json', false),
            array('stdClass', 'csv', false)
        );
    }

    /**
     * Test related method
     * @param mixed   $class
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
}
