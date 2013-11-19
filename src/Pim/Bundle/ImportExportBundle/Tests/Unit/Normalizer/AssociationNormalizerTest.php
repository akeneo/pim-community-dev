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
class AssociationNormalizerTest extends NormalizerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new AssociationNormalizer();
        $this->format     = 'json';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\Association', 'json',  true),
            array('Pim\Bundle\CatalogBundle\Entity\Association', 'xml', true),
            array('Pim\Bundle\CatalogBundle\Entity\Association', 'csv', false),
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
     * @return Association
     */
    protected function createEntity(array $data)
    {
        $association = new Association();
        $association->setCode($data['code']);

        foreach ($this->getLabels($data) as $locale => $label) {
            $translation = $association->getTranslation($locale);
            $translation->setLabel($label);
            $association->addTranslation($translation);
        }

        return $association;
    }

    /**
     * Returns label property
     * @param array $data
     * @return mixed
     */
    protected function getLabels($data)
    {
        return $data['label'];
    }
}
