<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\AssociationNormalizer;
use Pim\Bundle\CatalogBundle\Entity\Association;
use Pim\Bundle\CatalogBundle\Entity\AssociationTranslation;

/**
 * Association normalizer test
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AssociationNormalizer
     */
    protected $normalizer;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new AssociationNormalizer();
    }

    /**
     * Data provider for testing supportsNormalization method
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\Association', 'json',  true),
            array('Pim\Bundle\CatalogBundle\Entity\Association', 'csv', false),
            array('stdClass', 'json',  false),
            array('stdClass', 'csv', false),
        );
    }

    /**
     * Test supportsNormalization method
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
                    'label' => array('en_US' => 'My label', 'fr_FR' => 'Mon étiquette')
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
            $this->normalizer->normalize($association, 'json')
        );
    }

    /**
     * Create a association
     * @param array $data
     *
     * @return Association
     */
    protected function createAssociation(array $data)
    {
        $association = new Association();
        $association->setCode('mycode');

        $translations = array('en_US' => 'My label', 'fr_FR' => 'Mon étiquette');
        foreach ($translations as $locale => $label) {
            $translation = new AssociationTranslation();
            $translation->setLocale($locale);
            $translation->setLabel($label);
            $association->addTranslation($translation);
        }

        return $association;
    }
}
