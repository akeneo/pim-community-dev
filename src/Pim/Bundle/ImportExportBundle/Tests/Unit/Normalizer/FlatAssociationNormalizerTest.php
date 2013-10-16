<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\FlatAssociationNormalizer;
use Pim\Bundle\CatalogBundle\Entity\Association;

/**
 * Association normalizer test
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatAssociationNormalizerTest extends AssociationNormalizerTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new FlatAssociationNormalizer();
    }

    /**
     * Data provider for testing supportsNormalization method
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\Association', 'csv',  true),
            array('Pim\Bundle\CatalogBundle\Entity\Association', 'json', false),
            array('stdClass', 'csv',  false),
            array('stdClass', 'json', false),
        );
    }

    /**
     * Data provider for testing normalize method
     * @return array
     */
    public static function getNormalizeData()
    {
        return array(
            array(
                array(
                    'code'  => 'mycode',
                    'label' => 'en_US:My label, fr_FR:Mon étiquette',
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
        $association = $this->createAssociation($expectedResult);
        $this->assertEquals(
            $expectedResult,
            $this->normalizer->normalize($association, 'csv')
        );
    }
}
