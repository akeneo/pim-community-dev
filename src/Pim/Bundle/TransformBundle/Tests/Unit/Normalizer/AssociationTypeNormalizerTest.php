<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Normalizer;

use Pim\Bundle\TransformBundle\Normalizer\AssociationTypeNormalizer;
use Pim\Bundle\TransformBundle\Normalizer\TranslationNormalizer;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;

/**
 * Association type normalizer test
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeNormalizerTest extends NormalizerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new AssociationTypeNormalizer(new TranslationNormalizer());
        $this->format     = 'json';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\AssociationType', 'json',  true),
            array('Pim\Bundle\CatalogBundle\Entity\AssociationType', 'xml', true),
            array('Pim\Bundle\CatalogBundle\Entity\AssociationType', 'csv', false),
            array('stdClass', 'json',  false),
            array('stdClass', 'xml', false),
            array('stdClass', 'csv', false),
        );
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     *
     * @return AssociationType
     */
    protected function createEntity(array $data)
    {
        $associationType = new AssociationType();
        $associationType->setCode($data['code']);

        foreach ($this->getLabels($data) as $locale => $label) {
            $translation = $associationType->getTranslation($locale);
            $translation->setLabel($label);
            $associationType->addTranslation($translation);
        }

        return $associationType;
    }

    /**
     * Returns label property
     * @param array $data
     *
     * @return mixed
     */
    protected function getLabels($data)
    {
        return $data['label'];
    }
}
