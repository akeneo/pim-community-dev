<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\FlatAttributeGroupNormalizer;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

/**
 * Attribute group flat normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatAttributeGroupNormalizerTest extends AttributeGroupNormalizerTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new FlatAttributeGroupNormalizer();
    }

    /**
     * Data provider for testing supportsNormalization method
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\AttributeGroup', 'csv',  true),
            array('Pim\Bundle\CatalogBundle\Entity\AttributeGroup', 'json', false),
            array('stdClass', 'csv',  false),
            array('stdClass', 'json', false),
        );
    }

    /**
     * Data provider for testing normalize method
     * @return array
     * @static
     */
    public static function getNormalizeData()
    {
        return array(
            array(
                array(
                    'code'       => 'mycode',
                    'name'      => 'en_US:My name, fr_FR:Mon nom',
                    'sortOrder'  => 5,
                    'attributes' => 'attribute1, attribute2, attribute3'
                )
            ),
        );
    }

    /**
     * Test normalize method
     * @param array $expectedResult
     *
     * @dataProvider getNormalizeData
     */
    public function testNormalize(array $expectedResult)
    {
        $group = $this->createGroup($expectedResult);
        $this->assertEquals(
            $expectedResult,
            $this->normalizer->normalize($group, 'csv')
        );
    }
}
